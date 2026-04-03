<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Username Verification</title>

    <!-- Google Fonts: Distinctive display + body pairing -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <!-- External AJAX logic -->
    <script src="verify_ajax.js" defer></script>

    <style>
        /* ─── CSS Variables ─── */
        :root {
            --bg:         #0d0d0d;
            --card-bg:    #161616;
            --border:     #2a2a2a;
            --accent:     #00e676;
            --accent-dim: rgba(0, 230, 118, 0.12);
            --text:       #f0f0f0;
            --muted:      #666;
            --error:      #ff4d6d;
            --radius:     12px;
            --font-head:  'Syne', sans-serif;
            --font-body:  'DM Sans', sans-serif;
            --transition: 0.25s ease;
        }

        /* ─── Reset & Base ─── */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Prevent scrollbars from canvas */
        }

        /* ─── Animated Canvas Background ─── */
        #bg-canvas {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0; /* Behind everything */
            pointer-events: none;
        }

        /* Ensure card sits above the canvas */
        .card {
            z-index: 1;
        }

        /* ─── Card ─── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px 36px;
            width: 340px;
            box-shadow: 0 0 60px rgba(0, 230, 118, 0.04);
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.5s ease both;
        }

        /* Decorative accent stripe at the top of the card */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }

        /* ─── Heading ─── */
        .card-title {
            font-family: var(--font-head);
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text);
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 28px;
        }

        /* ─── Form Group ─── */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* ─── Input ─── */
        .form-group input {
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: var(--font-body);
            font-size: 0.95rem;
            padding: 11px 14px;
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
            width: 100%;
        }

        .form-group input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }

        /* ─── Button ─── */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: #000;
            font-family: var(--font-head);
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity var(--transition), transform var(--transition);
        }

        .btn-submit:hover {
            opacity: 0.88;
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ─── Result Message ─── */
        #result {
            margin-top: 18px;
            min-height: 22px;
            font-size: 0.88rem;
            font-weight: 500;
            text-align: center;
            letter-spacing: 0.02em;
            transition: color var(--transition), opacity var(--transition);
        }

        #result.success { color: var(--accent); }
        #result.error   { color: var(--error);  }
        #result.info    { color: var(--muted);  }

        /* ─── Animation ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
    </style>
</head>
<body>

    <!-- ── Animated Particle Background ── -->
    <canvas id="bg-canvas"></canvas>

    <!-- ── Verification Card ── -->
    <div class="card" role="main">

        <h1 class="card-title">Verify User</h1>
        <p class="card-subtitle">Enter your username to continue</p>

        <!-- Input Group -->
        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                placeholder="e.g. abc"
                autocomplete="username"
                aria-label="Username"
            >
        </div>

        <!-- Submit triggers AJAX verification in verify_ajax.js -->
        <button class="btn-submit" onclick="verifyUser()">Submit</button>

        <!-- Result injected dynamically by verify_ajax.js -->
        <p id="result" aria-live="polite"></p>

    </div>

    <!-- ── Background Particle Animation ── -->
    <script>
        /**
         * Draws an animated field of floating particles connected by
         * faint lines when close together, creating a moving "network"
         * effect behind the verification card.
         */
        (function () {
            const canvas  = document.getElementById("bg-canvas");
            const ctx     = canvas.getContext("2d");

            /* ── Config ── */
            const PARTICLE_COUNT  = 80;   // Number of floating dots
            const MAX_SPEED       = 0.5;  // Max velocity per axis
            const CONNECT_DIST    = 130;  // Pixel distance to draw connecting lines
            const DOT_RADIUS      = 1.8;
            const ACCENT_COLOR    = "0, 230, 118"; // RGB of --accent

            let particles = [];
            let W, H;

            /* ── Resize handler: keep canvas full-screen ── */
            function resize() {
                W = canvas.width  = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }

            /* ── Create a single particle with random position & velocity ── */
            function createParticle() {
                return {
                    x:  Math.random() * W,
                    y:  Math.random() * H,
                    vx: (Math.random() - 0.5) * MAX_SPEED * 2,
                    vy: (Math.random() - 0.5) * MAX_SPEED * 2,
                };
            }

            /* ── Initialise particle array ── */
            function init() {
                resize();
                particles = Array.from({ length: PARTICLE_COUNT }, createParticle);
            }

            /* ── Move a particle and bounce off edges ── */
            function updateParticle(p) {
                p.x += p.vx;
                p.y += p.vy;

                if (p.x < 0 || p.x > W) p.vx *= -1;
                if (p.y < 0 || p.y > H) p.vy *= -1;
            }

            /* ── Draw connecting line between two nearby particles ── */
            function drawConnection(p1, p2, distance) {
                // Opacity fades as the distance approaches the threshold
                const alpha = 1 - distance / CONNECT_DIST;
                ctx.strokeStyle = `rgba(${ACCENT_COLOR}, ${alpha * 0.25})`;
                ctx.lineWidth   = 0.6;
                ctx.beginPath();
                ctx.moveTo(p1.x, p1.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.stroke();
            }

            /* ── Main animation loop ── */
            function animate() {
                /* Clear with a translucent fill to create a motion-trail effect */
                ctx.fillStyle = "rgba(13, 13, 13, 0.25)";
                ctx.fillRect(0, 0, W, H);

                /* Update positions and draw each dot */
                for (const p of particles) {
                    updateParticle(p);

                    ctx.beginPath();
                    ctx.arc(p.x, p.y, DOT_RADIUS, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(${ACCENT_COLOR}, 0.6)`;
                    ctx.fill();
                }

                /* Check all pairs for proximity and draw connections */
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx   = particles[i].x - particles[j].x;
                        const dy   = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < CONNECT_DIST) {
                            drawConnection(particles[i], particles[j], dist);
                        }
                    }
                }

                requestAnimationFrame(animate);
            }

            /* ── Boot ── */
            window.addEventListener("resize", resize);
            init();
            animate();
        })();
    </script>

</body>
</html>