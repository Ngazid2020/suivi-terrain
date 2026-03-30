@php
    $height = $getHeight();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
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
                
                this.canvas.addEventListener('mousedown', (e) => this.startDraw(e));
                this.canvas.addEventListener('mousemove', (e) => this.draw(e));
                this.canvas.addEventListener('mouseup', () => this.stopDraw());
                this.canvas.addEventListener('mouseout', () => this.stopDraw());
                
                this.canvas.addEventListener('touchstart', (e) => this.startDraw(e));
                this.canvas.addEventListener('touchmove', (e) => this.draw(e));
                this.canvas.addEventListener('touchend', () => this.stopDraw());
            },
            resize() {
                const rect = this.canvas.parentElement.getBoundingClientRect();
                this.canvas.width = rect.width;
                this.canvas.height = parseInt('{{ $height }}');
                this.context.lineWidth = 2;
                this.context.lineCap = 'round';
                this.context.strokeStyle = '#000000';
            },
            getPos(e) {
                const rect = this.canvas.getBoundingClientRect();
                if(e.touches) {
                    return {
                        x: e.touches[0].clientX - rect.left,
                        y: e.touches[0].clientY - rect.top
                    };
                }
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
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
                const pos = this.getPos(e);
                this.context.lineTo(pos.x, pos.y);
                this.context.stroke();
                e.preventDefault();
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
                return dataURL === blank.toDataURL('image/png');
            },
            clear() {
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                $wire.set('{{ $statePath }}', null);
            }
        }"
        x-init="init()"
        @class([
            'relative border rounded-lg bg-white overflow-hidden touch-none',
            'border-gray-300' => ! $errors->has($statePath),
            'border-danger-500' => $errors->has($statePath),
        ])
        style="height: {{ $height }}"
    >
        <canvas
            x-ref="canvas"
            @class([
                'w-full h-full',
                'cursor-crosshair' => ! $isDisabled,
                'cursor-not-allowed opacity-50' => $isDisabled,
            ])
            @if($isDisabled) disabled @endif
        ></canvas>
        
        @if(! $isDisabled)
        <div class="absolute top-2 right-2">
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
            {{ $applyStateBindingAttributes('wire:model=' . $statePath) }}
        />
    </div>

    @if ($isRequired())
        <p class="text-xs text-gray-500 mt-1">
            Veuillez signer ci-dessus avant de soumettre.
        </p>
    @endif
</x-dynamic-component>