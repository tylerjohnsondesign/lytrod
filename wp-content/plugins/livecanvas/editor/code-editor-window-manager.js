(function (window) {
    "use strict";

    const MODES = Object.freeze({
        splitH: "split-h",
        splitV: "split-v",
        custom: "custom"
    });
    const FLOAT_EDGES = ["n", "e", "s", "w", "ne", "nw", "se", "sw"];

    function clamp(value, minimum, maximum) {
        return Math.min(Math.max(value, minimum), Math.max(minimum, maximum));
    }

    class LiveCanvasCodeEditorWindowManager {
        constructor(options) {
            this.options = options;
            this.element = options.element;
            this.dragHandle = options.dragHandle;
            this.dragSurface = options.dragSurface;
            this.mode = MODES.splitV;
            this.opened = false;
            this.geometry = null;
            this.interaction = null;
            this.frame = null;
            this.pendingPointer = null;
            this.resizeTimer = null;
            this.sashes = this.createSashes();

            this.onPointerMove = this.onPointerMove.bind(this);
            this.finishInteraction = this.finishInteraction.bind(this);
            this.onWindowBlur = this.onWindowBlur.bind(this);
            this.onWindowResize = this.onWindowResize.bind(this);

            this.dragHandle.addEventListener("pointerdown", (event) => this.startInteraction(event, "move"));
            this.dragSurface.addEventListener("pointerdown", (event) => {
                if (event.target.closest("a, button, input, select, textarea, label, [contenteditable], .lc-editor-view-options-panel, .lc-editor-resources-panel")) return;
                const action = this.mode === MODES.custom ? "move" : "split";
                this.startInteraction(event, action);
            });
            this.sashes.forEach((sash) => {
                sash.addEventListener("pointerdown", (event) => this.startInteraction(event, sash.dataset.edge));
                sash.addEventListener("keydown", (event) => this.onSashKeyDown(event, sash));
            });
            window.addEventListener("resize", this.onWindowResize);
        }

        createSashes() {
            return ["split", ...FLOAT_EDGES].map((edge) => {
                const sash = document.createElement("div");
                const isCorner = edge.length === 2;
                sash.className = "lc-code-editor-sash lc-code-editor-sash-" + edge;
                sash.dataset.edge = edge;
                sash.setAttribute("role", isCorner ? "presentation" : "separator");
                sash.setAttribute("tabindex", isCorner ? "-1" : "0");
                if (!isCorner) {
                    sash.setAttribute("aria-label", edge === "split" ? "Resize code editor and preview" : "Resize code editor window");
                    if (edge !== "split") sash.setAttribute("aria-orientation", edge === "e" || edge === "w" ? "vertical" : "horizontal");
                }
                this.element.append(sash);
                return sash;
            });
        }

        open(mode) {
            this.opened = true;
            this.element.hidden = false;
            this.element.style.display = "block";
            this.element.classList.add("lc-code-editor-managed-window");
            this.setMode(mode);
        }

        close() {
            if (!this.opened) return;
            this.persist();
            this.cancelInteraction();
            clearTimeout(this.resizeTimer);
            this.opened = false;
            this.element.hidden = true;
            this.element.style.display = "none";
            this.element.classList.remove("lc-code-editor-managed-window");
            document.body.classList.remove("lc-code-editor-split-mode", "lc-code-editor-split-v-mode", "lc-code-editor-floating-mode");
        }

        setMode(mode) {
            if (!Object.values(MODES).includes(mode)) mode = MODES.splitV;
            const previousMode = this.mode;
            if (this.opened && mode !== previousMode) this.persist();
            this.mode = mode;
            if (mode === MODES.custom && previousMode !== MODES.custom) {
                this.geometry = this.normalizeFloatGeometry(this.options.getFloatLayout());
            }
            document.body.classList.toggle("lc-code-editor-split-mode", mode === MODES.splitH);
            document.body.classList.toggle("lc-code-editor-split-v-mode", mode === MODES.splitV);
            document.body.classList.toggle("lc-code-editor-floating-mode", mode === MODES.custom);
            // Keep the handle hidden; the menubar provides the drag surface.
            this.dragHandle.hidden = true;
            const splitSash = this.sashes[0];
            splitSash.setAttribute("aria-orientation", mode === MODES.splitH ? "vertical" : "horizontal");
            this.layout();
        }

        layout() {
            if (!this.opened) return;
            if (this.mode === MODES.splitH) {
                this.geometry = this.getSplitHGeometry();
            } else if (this.mode === MODES.splitV) {
                this.geometry = this.getSplitVGeometry();
            } else {
                this.geometry = this.normalizeFloatGeometry(this.geometry || this.options.getFloatLayout());
            }
            this.applyGeometry(this.geometry);
            this.updateSeparatorValue();
            this.options.onLayout?.();
        }

        isOpen() {
            return this.opened;
        }

        getViewport() {
            const top = this.options.getToolbarHeight();
            return { top, width: window.innerWidth, height: window.innerHeight, availableHeight: window.innerHeight - top };
        }

        getSplitHGeometry() {
            const viewport = this.getViewport();
            const minimum = Math.min(320, viewport.width);
            const maximum = Math.max(minimum, viewport.width - 320);
            const width = clamp(this.options.getSplitWidth(), minimum, maximum);
            return { x: 0, y: viewport.top, width, height: viewport.availableHeight };
        }

        getSplitVGeometry() {
            const viewport = this.getViewport();
            const minimum = Math.min(240, viewport.availableHeight);
            const maximum = Math.max(minimum, viewport.availableHeight - 160);
            const height = clamp(this.options.getSplitHeight(), minimum, maximum);
            return { x: 0, y: viewport.height - height, width: viewport.width, height };
        }

        normalizeFloatGeometry(layout) {
            const viewport = this.getViewport();
            const minimumWidth = Math.min(450, viewport.width);
            const minimumHeight = Math.min(240, viewport.availableHeight);
            const width = clamp(Number(layout?.width) || 550, minimumWidth, viewport.width);
            const height = clamp(Number(layout?.height) || Math.round(viewport.availableHeight / 2), minimumHeight, viewport.availableHeight);
            const rawX = Number(layout?.x);
            const rawY = Number(layout?.y);
            const x = clamp(Number.isFinite(rawX) ? rawX : Math.round((viewport.width - width) / 2), 0, viewport.width - width);
            const y = clamp(Number.isFinite(rawY) ? rawY : viewport.height - height, viewport.top, viewport.height - height);
            return { x: Math.round(x), y: Math.round(y), width: Math.round(width), height: Math.round(height) };
        }

        applyGeometry(geometry) {
            this.element.style.left = geometry.x + "px";
            this.element.style.top = geometry.y + "px";
            this.element.style.width = geometry.width + "px";
            this.element.style.height = geometry.height + "px";
            this.element.style.right = "auto";
            this.element.style.bottom = "auto";
            if (this.mode === MODES.splitH) {
                document.documentElement.style.setProperty("--lc-code-editor-split-width", geometry.width + "px");
            } else if (this.mode === MODES.splitV) {
                document.documentElement.style.setProperty("--lc-code-editor-split-height", geometry.height + "px");
            }
        }

        startInteraction(event, action) {
            if (!this.opened || event.button !== 0) return;
            if (action === "move" && this.mode !== MODES.custom) return;
            if (action !== "move" && action !== "split" && this.mode !== MODES.custom) return;

            event.preventDefault();
            event.stopPropagation();
            const target = event.currentTarget;
            try {
                target.setPointerCapture(event.pointerId);
            } catch (error) {
                // Window-level listeners below keep the interaction usable when
                // a browser cannot capture this particular pointer target.
            }
            this.interaction = {
                action,
                pointerId: event.pointerId,
                target,
                startX: event.clientX,
                startY: event.clientY,
                startGeometry: { ...this.geometry }
            };
            target.addEventListener("lostpointercapture", this.finishInteraction);
            window.addEventListener("pointermove", this.onPointerMove, true);
            window.addEventListener("pointerup", this.finishInteraction, true);
            window.addEventListener("pointercancel", this.finishInteraction, true);
            window.addEventListener("blur", this.onWindowBlur);
            document.body.classList.add("lc-code-editor-interacting");
        }

        onPointerMove(event) {
            if (!this.interaction || event.pointerId !== this.interaction.pointerId) return;
            this.pendingPointer = { x: event.clientX, y: event.clientY };
            if (this.frame !== null) return;
            this.frame = requestAnimationFrame(() => {
                this.frame = null;
                if (!this.pendingPointer || !this.interaction) return;
                this.geometry = this.geometryFromPointer(this.pendingPointer.x, this.pendingPointer.y);
                this.applyGeometry(this.geometry);
                this.updateSeparatorValue();
                this.options.onLayout?.();
            });
        }

        geometryFromPointer(clientX, clientY) {
            const interaction = this.interaction;
            const start = interaction.startGeometry;
            const viewport = this.getViewport();
            const deltaX = clientX - interaction.startX;
            const deltaY = clientY - interaction.startY;

            if (interaction.action === "split") {
                if (this.mode === MODES.splitH) {
                    const minimum = Math.min(320, viewport.width);
                    const width = clamp(start.width + deltaX, minimum, Math.max(minimum, viewport.width - 320));
                    return { x: 0, y: viewport.top, width, height: viewport.availableHeight };
                }
                const minimum = Math.min(240, viewport.availableHeight);
                const height = clamp(start.height - deltaY, minimum, Math.max(minimum, viewport.availableHeight - 160));
                return { x: 0, y: viewport.height - height, width: viewport.width, height };
            }

            if (interaction.action === "move") {
                return this.normalizeFloatGeometry({ ...start, x: start.x + deltaX, y: start.y + deltaY });
            }

            let left = start.x;
            let top = start.y;
            let right = start.x + start.width;
            let bottom = start.y + start.height;
            const edge = interaction.action;
            if (edge.includes("w")) left += deltaX;
            if (edge.includes("e")) right += deltaX;
            if (edge.includes("n")) top += deltaY;
            if (edge.includes("s")) bottom += deltaY;

            const minimumWidth = Math.min(450, viewport.width);
            const minimumHeight = Math.min(240, viewport.availableHeight);
            left = clamp(left, 0, right - minimumWidth);
            right = clamp(right, left + minimumWidth, viewport.width);
            top = clamp(top, viewport.top, bottom - minimumHeight);
            bottom = clamp(bottom, top + minimumHeight, viewport.height);
            return { x: Math.round(left), y: Math.round(top), width: Math.round(right - left), height: Math.round(bottom - top) };
        }

        finishInteraction(event) {
            if (!this.interaction) return;
            if (event?.pointerId !== undefined && event.pointerId !== this.interaction.pointerId) return;

            const finalPointer = event?.type === "pointerup" && Number.isFinite(event.clientX) && Number.isFinite(event.clientY)
                ? { x: event.clientX, y: event.clientY }
                : this.pendingPointer;
            if (finalPointer) {
                this.geometry = this.geometryFromPointer(finalPointer.x, finalPointer.y);
                this.applyGeometry(this.geometry);
                this.updateSeparatorValue();
            }

            const { target, pointerId } = this.interaction;
            target.removeEventListener("lostpointercapture", this.finishInteraction);
            window.removeEventListener("pointermove", this.onPointerMove, true);
            window.removeEventListener("pointerup", this.finishInteraction, true);
            window.removeEventListener("pointercancel", this.finishInteraction, true);
            if (target.hasPointerCapture?.(pointerId)) target.releasePointerCapture(pointerId);
            window.removeEventListener("blur", this.onWindowBlur);
            this.interaction = null;
            this.pendingPointer = null;
            if (this.frame !== null) cancelAnimationFrame(this.frame);
            this.frame = null;
            document.body.classList.remove("lc-code-editor-interacting");
            this.persist();
            this.options.onLayout?.();
        }

        onWindowBlur() {
            this.finishInteraction();
        }

        onWindowResize() {
            if (!this.opened) return;
            this.layout();
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(() => this.persist(), 150);
        }

        cancelInteraction() {
            if (this.interaction) this.finishInteraction();
        }

        persist() {
            if (!this.geometry) return;
            if (this.mode === MODES.splitH) this.options.setSplitWidth(this.geometry.width);
            else if (this.mode === MODES.splitV) this.options.setSplitHeight(this.geometry.height);
            else if (this.mode === MODES.custom) this.options.setFloatLayout({ ...this.geometry });
        }

        updateSeparatorValue() {
            const splitSash = this.sashes[0];
            const value = this.mode === MODES.splitH ? this.geometry?.width : this.geometry?.height;
            const viewport = this.getViewport();
            const minimum = this.mode === MODES.splitH ? Math.min(320, viewport.width) : Math.min(240, viewport.availableHeight);
            const maximum = this.mode === MODES.splitH
                ? Math.max(minimum, viewport.width - 320)
                : Math.max(minimum, viewport.availableHeight - 160);
            splitSash.setAttribute("aria-valuemin", String(Math.round(minimum)));
            splitSash.setAttribute("aria-valuemax", String(Math.round(maximum)));
            splitSash.setAttribute("aria-valuenow", String(Math.round(value || 0)));
        }

        onSashKeyDown(event, sash) {
            if (!this.opened) return;
            const step = event.shiftKey ? 50 : 10;
            const edge = sash.dataset.edge;

            if (this.mode === MODES.custom && edge !== "split" && edge.length === 1) {
                let deltaX = 0;
                let deltaY = 0;
                if ((edge === "e" || edge === "w") && (event.key === "ArrowLeft" || event.key === "ArrowRight")) {
                    deltaX = event.key === "ArrowLeft" ? -step : step;
                } else if ((edge === "n" || edge === "s") && (event.key === "ArrowUp" || event.key === "ArrowDown")) {
                    deltaY = event.key === "ArrowUp" ? -step : step;
                } else return;

                event.preventDefault();
                this.interaction = { action: edge, startX: 0, startY: 0, startGeometry: { ...this.geometry } };
                this.geometry = this.geometryFromPointer(deltaX, deltaY);
                this.interaction = null;
                this.applyGeometry(this.geometry);
                this.persist();
                this.options.onLayout?.();
                return;
            }

            if (edge !== "split" || this.mode === MODES.custom) return;
            let delta = 0;
            if (this.mode === MODES.splitH && (event.key === "ArrowLeft" || event.key === "ArrowRight")) {
                delta = event.key === "ArrowLeft" ? -step : step;
            } else if (this.mode === MODES.splitV && (event.key === "ArrowUp" || event.key === "ArrowDown")) {
                delta = event.key === "ArrowUp" ? step : -step;
            } else return;

            event.preventDefault();
            if (this.mode === MODES.splitH) this.options.setSplitWidth(this.geometry.width + delta);
            else this.options.setSplitHeight(this.geometry.height + delta);
            this.layout();
            this.persist();
        }
    }

    window.LiveCanvasCodeEditorWindowManager = LiveCanvasCodeEditorWindowManager;
})(window);
