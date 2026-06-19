/**
 * REFEST Festival — Landing page WebGL scene
 * --------------------------------------------------
 * A self-contained Three.js module that mounts an interactive 3-D scene
 * into the welcome page.  Features:
 *   - full-viewport WebGL canvas
 *   - a slowly rotating low-poly festival "stage" geometry
 *   - a particle field of glowing "lights"
 *   - mouse-parallax camera tilt
 *   - automatic fallback to a static hero when WebGL is unavailable
 *
 * No external 3-D assets are required — everything is procedural.
 */

import * as THREE from 'three';

const PARALLAX_STRENGTH = 0.35;
const ROTATION_IDLE_SPEED = 0.0015;

export default function mountLandingScene(container) {
    if (!container) return null;
    if (!window.WebGLRenderingContext) {
        container.classList.add('landing--fallback');
        return null;
    }

    /* -------------------------------------------------
     * Renderer
     * ------------------------------------------------- */
    const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    container.appendChild(renderer.domElement);
    renderer.domElement.classList.add('landing__canvas');

    /* -------------------------------------------------
     * Scene & camera
     * ------------------------------------------------- */
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x0a0612, 0.06);

    const camera = new THREE.PerspectiveCamera(
        55,
        container.clientWidth / container.clientHeight,
        0.1,
        100
    );
    camera.position.set(0, 0, 8);

    /* -------------------------------------------------
     * Lights
     * ------------------------------------------------- */
    const ambient = new THREE.AmbientLight(0xff4d8d, 0.45);
    scene.add(ambient);

    const key = new THREE.DirectionalLight(0xff66cc, 1.4);
    key.position.set(5, 6, 5);
    scene.add(key);

    const rim = new THREE.DirectionalLight(0x5ce1ff, 1.2);
    rim.position.set(-6, -3, -4);
    scene.add(rim);

    const pulse = new THREE.PointLight(0xffffff, 2, 12, 2);
    pulse.position.set(0, 0, 2);
    scene.add(pulse);

    /* -------------------------------------------------
     * Festival "stage" — a low-poly torus knot
     * ------------------------------------------------- */
    const stageGeo = new THREE.TorusKnotGeometry(2.2, 0.55, 220, 28);
    const stageMat = new THREE.MeshStandardMaterial({
        color: 0xff2d92,
        metalness: 0.85,
        roughness: 0.18,
        emissive: 0x4a0a4f,
        emissiveIntensity: 0.45,
        flatShading: true,
    });
    const stage = new THREE.Mesh(stageGeo, stageMat);
    scene.add(stage);

    /* An outer wireframe shell to add depth */
    const wireMat = new THREE.MeshBasicMaterial({
        color: 0x5ce1ff,
        wireframe: true,
        transparent: true,
        opacity: 0.35,
    });
    const wire = new THREE.Mesh(stageGeo.clone(), wireMat);
    wire.scale.setScalar(1.08);
    scene.add(wire);

    /* -------------------------------------------------
     * Particle field — "festival lights"
     * ------------------------------------------------- */
    const particleCount = 600;
    const positions = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 3);
    const palette = [
        new THREE.Color(0xff2d92),
        new THREE.Color(0x5ce1ff),
        new THREE.Color(0xffffff),
        new THREE.Color(0xffc94d),
    ];

    for (let i = 0; i < particleCount; i++) {
        const r = 6 + Math.random() * 12;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        positions[i * 3 + 0] = r * Math.sin(phi) * Math.cos(theta);
        positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
        positions[i * 3 + 2] = r * Math.cos(phi) - 4;

        const c = palette[Math.floor(Math.random() * palette.length)];
        colors[i * 3 + 0] = c.r;
        colors[i * 3 + 1] = c.g;
        colors[i * 3 + 2] = c.b;
    }

    const particleGeo = new THREE.BufferGeometry();
    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particleGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const particleMat = new THREE.PointsMaterial({
        size: 0.08,
        sizeAttenuation: true,
        vertexColors: true,
        transparent: true,
        opacity: 0.85,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    const particles = new THREE.Points(particleGeo, particleMat);
    scene.add(particles);

    /* -------------------------------------------------
     * State + event handlers
     * ------------------------------------------------- */
    const mouse = { x: 0, y: 0, tx: 0, ty: 0 };

    function onPointerMove(event) {
        const t = event.touches ? event.touches[0] : event;
        mouse.tx = (t.clientX / window.innerWidth) * 2 - 1;
        mouse.ty = -(t.clientY / window.innerHeight) * 2 + 1;
    }

    window.addEventListener('pointermove', onPointerMove, { passive: true });
    window.addEventListener('touchmove', onPointerMove, { passive: true });

    function onResize() {
        const w = container.clientWidth;
        const h = container.clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    window.addEventListener('resize', onResize);
    onResize();

    /* -------------------------------------------------
     * Visibility — pause when tab is hidden
     * ------------------------------------------------- */
    let visible = !document.hidden;
    document.addEventListener('visibilitychange', () => {
        visible = !document.hidden;
    });

    /* -------------------------------------------------
     * Animation loop
     * ------------------------------------------------- */
    let raf;
    const start = performance.now();
    function tick(now) {
        raf = requestAnimationFrame(tick);
        if (!visible) return;

        const t = (now - start) / 1000;

        // smooth mouse follow
        mouse.x += (mouse.tx - mouse.x) * 0.05;
        mouse.y += (mouse.ty - mouse.y) * 0.05;

        // idle rotation
        stage.rotation.y += ROTATION_IDLE_SPEED;
        stage.rotation.x = Math.sin(t * 0.3) * 0.15;
        wire.rotation.copy(stage.rotation);

        // parallax camera tilt
        camera.position.x = mouse.x * PARALLAX_STRENGTH;
        camera.position.y = mouse.y * PARALLAX_STRENGTH * 0.6;
        camera.lookAt(0, 0, 0);

        // pulsing point light
        pulse.intensity = 1.5 + Math.sin(t * 3) * 0.7;
        pulse.color.setHSL((t * 0.05) % 1, 0.8, 0.6);

        // slow particle drift
        particles.rotation.y = t * 0.02;
        particles.rotation.x = Math.sin(t * 0.1) * 0.05;

        renderer.render(scene, camera);
    }
    raf = requestAnimationFrame(tick);

    /* -------------------------------------------------
     * Cleanup hook
     * ------------------------------------------------- */
    return function destroy() {
        cancelAnimationFrame(raf);
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('touchmove', onPointerMove);
        window.removeEventListener('resize', onResize);

        stageGeo.dispose();
        stageMat.dispose();
        wireMat.dispose();
        particleGeo.dispose();
        particleMat.dispose();
        renderer.dispose();
        if (renderer.domElement.parentNode) {
            renderer.domElement.parentNode.removeChild(renderer.domElement);
        }
    };
}