/**
 * R-E — WebGL Particle System
 * Ambient floating particles with mouse interaction
 * Powered by Three.js
 */

(function () {
    'use strict';

    const canvas = document.getElementById('re-particles');
    if (!canvas || !document.body.classList.contains('re-particles-active')) return;
    if (typeof THREE === 'undefined') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (window.innerWidth < 900) return;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 300;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: false });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const PARTICLE_COUNT = 800;
    const geometry = new THREE.BufferGeometry();
    const positions = new Float32Array(PARTICLE_COUNT * 3);
    const velocities = new Float32Array(PARTICLE_COUNT * 3);
    const sizes = new Float32Array(PARTICLE_COUNT);

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 800;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 600;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 400;

        velocities[i * 3] = (Math.random() - 0.5) * 0.15;
        velocities[i * 3 + 1] = (Math.random() - 0.5) * 0.15;
        velocities[i * 3 + 2] = (Math.random() - 0.5) * 0.1;

        sizes[i] = Math.random() * 2 + 0.5;
    }

    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

    const material = new THREE.PointsMaterial({
        color: 0xF2A03D,
        size: 1.5,
        transparent: true,
        opacity: 0.4,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
        depthWrite: false,
    });

    const particles = new THREE.Points(geometry, material);
    scene.add(particles);

    // Connection lines between nearby particles
    const lineGeometry = new THREE.BufferGeometry();
    const MAX_LINES = 300;
    const linePositions = new Float32Array(MAX_LINES * 6);
    lineGeometry.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
    lineGeometry.setDrawRange(0, 0);

    const lineMaterial = new THREE.LineBasicMaterial({
        color: 0xF2A03D,
        transparent: true,
        opacity: 0.08,
        blending: THREE.AdditiveBlending,
        depthWrite: false,
    });

    const lines = new THREE.LineSegments(lineGeometry, lineMaterial);
    scene.add(lines);

    const mouse = { x: 0, y: 0 };

    document.addEventListener('mousemove', function (e) {
        mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;
    });

    function animate() {
        requestAnimationFrame(animate);

        const posArr = particles.geometry.attributes.position.array;
        let lineIdx = 0;
        let lineCount = 0;
        const lineArr = lines.geometry.attributes.position.array;
        const CONNECTION_DIST = 80;

        for (let i = 0; i < PARTICLE_COUNT; i++) {
            const ix = i * 3;
            posArr[ix] += velocities[ix];
            posArr[ix + 1] += velocities[ix + 1];
            posArr[ix + 2] += velocities[ix + 2];

            // Wrap around
            if (posArr[ix] > 400) posArr[ix] = -400;
            if (posArr[ix] < -400) posArr[ix] = 400;
            if (posArr[ix + 1] > 300) posArr[ix + 1] = -300;
            if (posArr[ix + 1] < -300) posArr[ix + 1] = 300;

            // Mouse influence
            const dx = posArr[ix] - mouse.x * 200;
            const dy = posArr[ix + 1] - mouse.y * 200;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 120) {
                const force = (120 - dist) / 120 * 0.3;
                posArr[ix] += (dx / dist) * force;
                posArr[ix + 1] += (dy / dist) * force;
            }

            // Build connection lines
            if (lineCount < MAX_LINES) {
                for (let j = i + 1; j < PARTICLE_COUNT && lineCount < MAX_LINES; j++) {
                    const jx = j * 3;
                    const ddx = posArr[ix] - posArr[jx];
                    const ddy = posArr[ix + 1] - posArr[jx + 1];
                    const ddz = posArr[ix + 2] - posArr[jx + 2];
                    const d = ddx * ddx + ddy * ddy + ddz * ddz;
                    if (d < CONNECTION_DIST * CONNECTION_DIST) {
                        lineArr[lineIdx++] = posArr[ix];
                        lineArr[lineIdx++] = posArr[ix + 1];
                        lineArr[lineIdx++] = posArr[ix + 2];
                        lineArr[lineIdx++] = posArr[jx];
                        lineArr[lineIdx++] = posArr[jx + 1];
                        lineArr[lineIdx++] = posArr[jx + 2];
                        lineCount++;
                    }
                }
            }
        }

        particles.geometry.attributes.position.needsUpdate = true;
        lines.geometry.attributes.position.needsUpdate = true;
        lines.geometry.setDrawRange(0, lineCount * 2);

        // Subtle camera sway
        camera.position.x += (mouse.x * 30 - camera.position.x) * 0.02;
        camera.position.y += (mouse.y * 20 - camera.position.y) * 0.02;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    }

    animate();

    window.addEventListener('resize', function () {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();
