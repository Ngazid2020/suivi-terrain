@php
    $height = $getHeight();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $isRequired = $isRequired();
    // Extraire la valeur numérique de la hauteur (ex: "250px" -> 250)
    $heightValue = intval(preg_replace('/[^0-9]/', '', $height));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            canvas: null,
            context: null,
            drawing: false,
            init() {
                this.canvas = this.$refs.canvas;
                this.context = this.canvas.getContext('2d');
                this.resize();
                
                // Événements Souris
                this.canvas.addEventListener('mousedown', (e) => this.startDraw(e));
                document.addEventListener('mousemove', (e) => this.draw(e));
                document.addEventListener('mouseup', () => this.stopDraw());
                
                // Événements Tactiles (Mobile)
                this.canvas.addEventListener('touchstart', (e) => this.startDraw(e));
                document.addEventListener('touchmove', (e) => this.draw(e));
                document.addEventListener('touchend', () => this.stopDraw());
            },
            resize() {
                // Obtenir la taille d'affichage CSS
                const rect = this.canvas.getBoundingClientRect();
                
                // IMPORTANT : Définir la résolution interne du canvas égale à la taille d'affichage
                // Cela évite l'effet de zoom/déformation
                this.canvas.width = rect.width;
                this.canvas.height = {{ $heightValue }};
                
                // Configuration du trait pour une meilleure visibilité
                this.context.lineWidth = 3; // Plus épais pour être visible
                this.context.lineCap = 'round';
                this.context.lineJoin = 'round';
                this.context.strokeStyle = '#000000';
                
                // Ajouter un fond blanc (sinon le PNG est transparent)
                this.context.fillStyle = '#FFFFFF';
                this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
            },
            getPos(e) {
                const rect = this.canvas.getBoundingClientRect();
                
                let clientX, clientY;
                if(e.touches && e.touches.length > 0) {
                    clientX = e.touches[0].clientX;
                    clientY = e.touches[0].clientY;
                } else {
                    clientX = e.clientX;
                    clientY = e.clientY;
                }
                
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            },
            startDraw(e) {
                this.drawing = true;
                const pos = this.getPos(e);
                this.context.beginPath();
                this.context.moveTo(pos.x, pos.y);
                e.preventDefault();
            },
            draw(e) {
                if (!this.drawing) return;
                e.preventDefault();
                const pos = this.getPos(e);
                this.context.lineTo(pos.x, pos.y);
                this.context.stroke();
            },
            stopDraw() {
                this.drawing = false;
                this.save();
            },
            save() {
                const dataURL = this.canvas.toDataURL('image/png');
                if (this.isEmpty(dataURL)) {
                    $wire.set('{{ $statePath }}', null);
                } else {
                    $wire.set('{{ $statePath }}', dataURL);
                }
            },
            isEmpty(dataURL) {
                const blank = document.createElement('canvas');
                blank.width = this.canvas.width;
                blank.height = this.canvas.height;
                const blankCtx = blank.getContext('2d');
                blankCtx.fillStyle = '#FFFFFF';
                blankCtx.fillRect(0, 0, blank.width, blank.height);
                return dataURL === blank.toDataURL('image/png');
            },
            clear() {
                this.context.fillStyle = '#FFFFFF';
                this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
                $wire.set('{{ $statePath }}', null);
            }
        }"
        x-init="init()"
        class="relative border border-gray-300 rounded-lg bg-white overflow-hidden touch-none"
        style="height: {{ $height }};"
    >
        <canvas
            x-ref="canvas"
            class="w-full h-full block"
            style="display: block; margin: 0; padding: 0; touch-action: none;"
        ></canvas>
        
        @if(! $isDisabled)
        <div class="absolute top-2 right-2 z-10">
            <button
                type="button"
                @click="clear()"
                class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200"
            >
                Effacer
            </button>
        </div>
        @endif
        
        <input
            type="hidden"
            wire:model="{{ $statePath }}"
        />
    </div>

    @if ($isRequired)
        <p class="text-xs text-gray-500 mt-1">
            Veuillez signer ci-dessus avant de soumettre.
        </p>
    @endif
</x-dynamic-component>