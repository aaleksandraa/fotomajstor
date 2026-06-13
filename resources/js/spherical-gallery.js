import * as THREE from 'three';
import { gsap } from 'gsap';

const root = document.querySelector('[data-spherical-gallery]');

if (root) {
    const canvas = root.querySelector('canvas');
    const loader = root.querySelector('[data-gallery-loader]');
    const progress = root.querySelector('[data-gallery-progress]');
    const hint = root.querySelector('[data-gallery-hint]');
    const modal = root.querySelector('[data-gallery-modal]');
    const modalImage = root.querySelector('[data-gallery-modal-image]');
    const closeButton = root.querySelector('[data-gallery-close]');
    const rawImages = JSON.parse(root.querySelector('[data-gallery-images]').textContent);
    const criticalImageCount = Math.min(rawImages.length, 4);

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x050505);
    scene.fog = new THREE.FogExp2(0x050505, 0.032);

    const camera = new THREE.PerspectiveCamera(68, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.rotation.order = 'YXZ';

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const gallery = new THREE.Group();
    scene.add(gallery);

    const raycaster = new THREE.Raycaster();
    const pointer = new THREE.Vector2();
    const clock = new THREE.Clock();
    const cards = [];
    const shaderUniforms = {
        time: { value: 0 },
        motion: { value: 0 },
        direction: { value: new THREE.Vector2() },
    };
    const state = {
        dragging: false,
        moved: false,
        startX: 0,
        startY: 0,
        lastX: 0,
        lastY: 0,
        yaw: 0,
        vertical: 0,
        targetYaw: 0,
        targetVertical: 0,
        velocityX: 0,
        velocityY: 0,
        hovered: null,
        modalOpen: false,
        activePointerId: null,
        pressedCard: null,
        pointerType: 'mouse',
        motion: 0,
    };

    let verticalSpan = 1;

    const wrapCentered = (value, span) => THREE.MathUtils.euclideanModulo(value + span / 2, span) - span / 2;

    function pointerPosition(event) {
        pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
        pointer.y = -(event.clientY / window.innerHeight) * 2 + 1;
    }

    function intersections(event) {
        pointerPosition(event);
        raycaster.setFromCamera(pointer, camera);
        return raycaster.intersectObjects(cards, false);
    }

    function setHovered(mesh) {
        if (state.hovered === mesh) return;

        if (state.hovered) {
            gsap.to(state.hovered.scale, { x: 1, y: 1, z: 1, duration: 0.45, ease: 'power3.out' });
            gsap.to(state.hovered.material, { opacity: 0.9, duration: 0.35 });
        }

        state.hovered = mesh;
        root.classList.toggle('is-hovering', Boolean(mesh));

        if (mesh) {
            gsap.to(mesh.scale, { x: 1.075, y: 1.075, z: 1.075, duration: 0.45, ease: 'power3.out' });
            gsap.to(mesh.material, { opacity: 1, duration: 0.35 });
        }
    }

    function openImage(mesh) {
        if (! mesh || state.modalOpen) return;
        state.modalOpen = true;
        modalImage.src = mesh.userData.src;
        modalImage.alt = mesh.userData.alt || '';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        gsap.killTweensOf(modalImage);
        gsap.fromTo(modal, { opacity: 0 }, { opacity: 1, duration: 0.35, ease: 'power2.out' });
        gsap.fromTo(
            modalImage,
            { opacity: 0, scale: 0.68, rotate: -2 },
            { opacity: 1, scale: 1, rotate: 0, duration: 0.8, ease: 'expo.out' },
        );
    }

    function closeImage() {
        if (! state.modalOpen) return;

        gsap.to(modalImage, {
            opacity: 0,
            scale: 0.76,
            duration: 0.35,
            ease: 'power3.in',
            onComplete: () => {
                state.modalOpen = false;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                modalImage.src = '';
            },
        });
        gsap.to(modal, { opacity: 0, duration: 0.4, ease: 'power2.in' });
    }

    function createFallbackTexture(index) {
        const colors = [0xb36d46, 0x49685c, 0x6b536f, 0x4a6382, 0x8a744e];
        const data = new Uint8Array([
            (colors[index % colors.length] >> 16) & 255,
            (colors[index % colors.length] >> 8) & 255,
            colors[index % colors.length] & 255,
            255,
        ]);
        const texture = new THREE.DataTexture(data, 1, 1);
        texture.needsUpdate = true;
        return texture;
    }

    function prepareTexture(texture) {
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.anisotropy = Math.min(renderer.capabilities.getMaxAnisotropy(), 4);

        return texture;
    }

    function addMotionShader(material) {
        material.onBeforeCompile = (shader) => {
            shader.uniforms.uGalleryTime = shaderUniforms.time;
            shader.uniforms.uGalleryMotion = shaderUniforms.motion;
            shader.uniforms.uGalleryDirection = shaderUniforms.direction;
            shader.vertexShader = shader.vertexShader
                .replace(
                    '#include <common>',
                    `#include <common>
                    uniform float uGalleryTime;
                    uniform float uGalleryMotion;
                    uniform vec2 uGalleryDirection;
                    varying vec2 vGalleryUv;`,
                )
                .replace(
                    '#include <begin_vertex>',
                    `#include <begin_vertex>
                    vGalleryUv = uv;
                    float galleryWave = sin((uv.y * 5.0) + (uv.x * 2.5) + (uGalleryTime * 1.6));
                    transformed.z += galleryWave * uGalleryMotion * 0.11;
                    transformed.x += (uv.y - 0.5) * uGalleryDirection.x * uGalleryMotion * 0.16;
                    transformed.y += (uv.x - 0.5) * uGalleryDirection.y * uGalleryMotion * 0.12;`,
                );
            shader.fragmentShader = shader.fragmentShader
                .replace(
                    '#include <common>',
                    `#include <common>
                    varying vec2 vGalleryUv;`,
                )
                .replace(
                    '#include <opaque_fragment>',
                    `float galleryEdge = min(min(vGalleryUv.x, 1.0 - vGalleryUv.x), min(vGalleryUv.y, 1.0 - vGalleryUv.y));
                    diffuseColor.rgb *= mix(0.72, 1.0, smoothstep(0.0, 0.13, galleryEdge));
                    #include <opaque_fragment>`,
                );
        };
        material.customProgramCacheKey = () => 'fotomajstor-gallery-motion-v1';
    }

    function loadTexture(textureLoader, image, index) {
        return textureLoader.loadAsync(image.src)
            .then((texture) => prepareTexture(texture))
            .catch(() => createFallbackTexture(index));
    }

    function hydrateCards(imageIndex, texture) {
        cards
            .filter((card) => card.userData.imageIndex === imageIndex)
            .forEach((card) => {
                gsap.to(card.material, {
                    opacity: 0.35,
                    duration: 0.18,
                    ease: 'power2.in',
                    onComplete: () => {
                        card.material.map = texture;
                        card.material.needsUpdate = true;
                        card.userData.textureLoaded = true;
                        gsap.to(card.material, { opacity: 0.9, duration: 0.45, ease: 'power2.out' });
                    },
                });
            });
    }

    async function loadInBackground(textureLoader, images, startIndex, concurrency = 3) {
        let nextIndex = startIndex;

        async function worker() {
            while (nextIndex < images.length) {
                const index = nextIndex++;
                const texture = await loadTexture(textureLoader, images[index], index);
                hydrateCards(index, texture);
            }
        }

        await Promise.all(Array.from({ length: Math.min(concurrency, images.length - startIndex) }, worker));
    }

    function scheduleBackgroundLoad(textureLoader) {
        const start = () => loadInBackground(textureLoader, rawImages, criticalImageCount);

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(start, { timeout: 900 });
        } else {
            window.setTimeout(start, 350);
        }
    }

    async function buildGallery() {
        if (! rawImages.length) {
            loader.querySelector('p').textContent = 'Portfolio trenutno nema fotografija.';
            return;
        }

        const textureLoader = new THREE.TextureLoader();
        textureLoader.setCrossOrigin('anonymous');
        const criticalImages = rawImages.slice(0, criticalImageCount);
        const criticalTextures = await Promise.all(
            criticalImages.map((image, index) => loadTexture(textureLoader, image, index)),
        );
        progress.textContent = `${criticalImageCount} / ${rawImages.length}`;

        const mobile = window.innerWidth < 700;
        const rows = mobile ? 10 : 11;
        const columns = mobile ? 16 : 20;
        const radius = mobile ? 8.2 : 9.6;
        const rowGap = 2.7;
        verticalSpan = rows * rowGap;
        let cellIndex = 0;

        for (let row = 0; row < rows; row++) {
            const baseY = (row - (rows - 1) / 2) * rowGap;
            const stagger = (row % 2) * (Math.PI * 2 / columns / 2);

            for (let column = 0; column < columns; column++) {
                const imageIndex = cellIndex % rawImages.length;
                const image = rawImages[imageIndex];
                const textureLoaded = imageIndex < criticalImageCount;
                const texture = criticalTextures[imageIndex % criticalTextures.length];
                const longitude = (column / columns) * Math.PI * 2 + stagger;
                const source = texture.image;
                const aspect = source?.width && source?.height ? source.width / source.height : 1.25;
                const width = window.innerWidth < 700 ? 2.85 : 2.92;
                const height = THREE.MathUtils.clamp(width / aspect, 1.95, 2.55);
                const geometry = new THREE.PlaneGeometry(width, height);
                const material = new THREE.MeshBasicMaterial({
                    map: texture,
                    transparent: true,
                    opacity: textureLoaded ? 0.9 : 0.58,
                    side: THREE.FrontSide,
                    toneMapped: false,
                });
                addMotionShader(material);
                const mesh = new THREE.Mesh(geometry, material);

                mesh.position.set(
                    radius * Math.sin(longitude),
                    baseY,
                    -radius * Math.cos(longitude),
                );
                mesh.lookAt(0, baseY, 0);
                mesh.userData = { ...image, imageIndex, textureLoaded, baseY };
                gallery.add(mesh);
                cards.push(mesh);
                cellIndex++;
            }
        }

        gsap.to(loader, {
            opacity: 0,
            duration: 0.6,
            ease: 'power2.out',
            onComplete: () => loader.remove(),
        });
        gsap.from(cards.map((card) => card.scale), {
            x: 0.82,
            y: 0.82,
            z: 0.82,
            duration: 1.4,
            stagger: { each: 0.006, from: 'random' },
            ease: 'expo.out',
        });
        window.setTimeout(() => hint.classList.add('is-hidden'), 4200);
        scheduleBackgroundLoad(textureLoader);
    }

    canvas.addEventListener('pointerdown', (event) => {
        if (state.modalOpen || ! event.isPrimary) return;
        state.dragging = true;
        state.moved = false;
        state.activePointerId = event.pointerId;
        state.pointerType = event.pointerType;
        state.pressedCard = intersections(event)[0]?.object ?? null;
        state.startX = state.lastX = event.clientX;
        state.startY = state.lastY = event.clientY;
        state.velocityX = 0;
        state.velocityY = 0;
        canvas.setPointerCapture(event.pointerId);
        root.classList.add('is-dragging');
    });

    canvas.addEventListener('pointermove', (event) => {
        if (state.modalOpen) return;

        if (! state.dragging) {
            setHovered(intersections(event)[0]?.object ?? null);
            return;
        }

        if (event.pointerId !== state.activePointerId) return;

        const deltaX = event.clientX - state.lastX;
        const deltaY = event.clientY - state.lastY;
        const dragThreshold = state.pointerType === 'touch' ? 16 : 7;
        state.moved ||= Math.hypot(event.clientX - state.startX, event.clientY - state.startY) > dragThreshold;
        state.targetYaw -= deltaX * 0.0036;
        state.targetVertical -= deltaY * 0.012;
        state.velocityX = -deltaX * 0.0015;
        state.velocityY = -deltaY * 0.005;
        state.lastX = event.clientX;
        state.lastY = event.clientY;
    });

    canvas.addEventListener('pointerup', (event) => {
        if (event.pointerId !== state.activePointerId) return;

        const tappedCard = state.pressedCard ?? intersections(event)[0]?.object ?? null;
        root.classList.remove('is-dragging');
        state.dragging = false;
        state.activePointerId = null;
        state.pressedCard = null;
        if (canvas.hasPointerCapture(event.pointerId)) canvas.releasePointerCapture(event.pointerId);
        if (! state.moved) openImage(tappedCard);
    });

    canvas.addEventListener('pointercancel', () => {
        state.dragging = false;
        state.activePointerId = null;
        state.pressedCard = null;
        root.classList.remove('is-dragging');
    });

    canvas.addEventListener('wheel', (event) => {
        event.preventDefault();
        state.targetYaw -= event.deltaX * 0.00075;
        state.targetVertical -= event.deltaY * 0.006;
        state.velocityX = -event.deltaX * 0.00008;
        state.velocityY = -event.deltaY * 0.0008;
    }, { passive: false });

    closeButton.addEventListener('click', closeImage);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeImage();
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeImage();
    });
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    function render() {
        shaderUniforms.time.value += Math.min(clock.getDelta(), 0.05);

        if (! state.dragging && ! state.modalOpen) {
            state.targetYaw += state.velocityX;
            state.targetVertical += state.velocityY;
            state.velocityX *= 0.94;
            state.velocityY *= 0.94;
        }

        state.yaw += (state.targetYaw - state.yaw) * 0.075;
        state.vertical += (state.targetVertical - state.vertical) * 0.075;
        const motionTarget = Math.min(
            Math.abs(state.targetYaw - state.yaw) * 1.8
            + Math.abs(state.targetVertical - state.vertical) * 0.1
            + Math.abs(state.velocityX) * 14
            + Math.abs(state.velocityY) * 4,
            1,
        );
        state.motion += (motionTarget - state.motion) * 0.09;
        shaderUniforms.motion.value = state.motion;
        shaderUniforms.direction.value.set(
            THREE.MathUtils.clamp(state.velocityX * 24, -1, 1),
            THREE.MathUtils.clamp(state.velocityY * 8, -1, 1),
        );
        camera.rotation.y = state.yaw;
        camera.rotation.z += ((state.velocityX * 0.8) - camera.rotation.z) * 0.055;
        cards.forEach((card) => {
            card.position.y = wrapCentered(card.userData.baseY + state.vertical, verticalSpan);
        });
        renderer.render(scene, camera);
        requestAnimationFrame(render);
    }

    buildGallery();
    render();
}
