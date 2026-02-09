<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close', 'minimize']);

const display = ref('0');
const history = ref('');
const isOpen = ref(true);

const position = ref({ x: window.innerWidth - 350, y: 100 });
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });

const appendNumber = (num) => {
    if (display.value === '0' || display.value === 'Error') {
        display.value = num.toString();
    } else {
        display.value += num.toString();
    }
};

const appendOperator = (op) => {
    const lastChar = display.value.slice(-1);
    if (['+', '-', '*', '/'].includes(lastChar)) {
        display.value = display.value.slice(0, -1) + op;
    } else {
        display.value += op;
    }
};

const clear = () => {
    display.value = '0';
    history.value = '';
};

const calculate = () => {
    try {
        const expression = display.value;
        // Basic evaluation
        const result = new Function('return ' + expression)();
        const formattedResult = Number.isInteger(result) ? result.toString() : result.toFixed(2).toString();
        
        history.value = expression + ' = ' + formattedResult;
        display.value = formattedResult;
    } catch (e) {
        display.value = 'Error';
        setTimeout(() => display.value = '0', 1000);
    }
};

const startDrag = (e) => {
    if (e.target.closest('.drag-handle')) {
        isDragging.value = true;
        dragOffset.value = {
            x: e.clientX - position.value.x,
            y: e.clientY - position.value.y
        };
        window.addEventListener('mousemove', handleDrag);
        window.addEventListener('mouseup', stopDrag);
    }
};

const handleDrag = (e) => {
    if (isDragging.value) {
        position.value = {
            x: e.clientX - dragOffset.value.x,
            y: e.clientY - dragOffset.value.y
        };
    }
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', handleDrag);
    window.removeEventListener('mouseup', stopDrag);
};

const handleKeyDown = (e) => {
    if (!props.show) return;
    
    if (e.key >= '0' && e.key <= '9') appendNumber(e.key);
    if (['+', '-', '*', '/'].includes(e.key)) appendOperator(e.key);
    if (e.key === 'Enter' || e.key === '=') calculate();
    if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') clear();
    if (e.key === 'Backspace') {
        display.value = display.value.length > 1 ? display.value.slice(0, -1) : '0';
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div 
        v-if="show"
        class="fixed z-[9999] w-72 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col"
        :style="{ left: position.x + 'px', top: position.y + 'px' }"
    >
        <!-- Header / Drag Handle -->
        <div 
            @mousedown="startDrag"
            class="drag-handle h-10 bg-gray-100 dark:bg-gray-700 flex items-center justify-between px-4 cursor-move select-none"
        >
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">{{ $t('Calculator') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <button @click="$emit('minimize')" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Screen -->
        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex flex-col justify-end min-h-[96px]">
            <div class="text-right text-xs font-mono text-gray-500 dark:text-gray-400 h-4 truncate mb-1">
                {{ history }}
            </div>
            <div class="text-right text-3xl font-mono font-bold text-gray-800 dark:text-white truncate">
                {{ display }}
            </div>
        </div>

        <!-- Buttons -->
        <div class="p-4 grid grid-cols-4 gap-2">
            <!-- Row 1 -->
            <button @click="clear" class="btn-calc btn-functional">C</button>
            <button @click="appendOperator('/')" class="btn-calc btn-operator">÷</button>
            <button @click="appendOperator('*')" class="btn-calc btn-operator">×</button>
            <button @click="display = display.length > 1 ? display.slice(0, -1) : '0'" class="btn-calc btn-functional">⌫</button>
            
            <!-- Row 2 -->
            <button @click="appendNumber(7)" class="btn-calc">7</button>
            <button @click="appendNumber(8)" class="btn-calc">8</button>
            <button @click="appendNumber(9)" class="btn-calc">9</button>
            <button @click="appendOperator('-')" class="btn-calc btn-operator">−</button>
            
            <!-- Row 3 -->
            <button @click="appendNumber(4)" class="btn-calc">4</button>
            <button @click="appendNumber(5)" class="btn-calc">5</button>
            <button @click="appendNumber(6)" class="btn-calc">6</button>
            <button @click="appendOperator('+')" class="btn-calc btn-operator">+</button>
            
            <!-- Row 4 -->
            <button @click="appendNumber(1)" class="btn-calc">1</button>
            <button @click="appendNumber(2)" class="btn-calc">2</button>
            <button @click="appendNumber(3)" class="btn-calc">3</button>
            <button @click="calculate" class="btn-calc btn-equal row-span-2">=</button>
            
            <!-- Row 5 -->
            <button @click="appendNumber(0)" class="btn-calc col-span-2">0</button>
            <button @click="appendNumber('.')" class="btn-calc">.</button>
        </div>
    </div>
</template>

<style scoped>
.btn-calc {
    @apply h-12 rounded-xl text-lg font-bold transition-all active:scale-95 flex items-center justify-center;
    @apply bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600;
}
.btn-operator {
    @apply bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40;
}
.btn-functional {
    @apply bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40;
}
.btn-equal {
    @apply bg-blue-600 text-white hover:bg-blue-700 h-auto;
}
</style>
