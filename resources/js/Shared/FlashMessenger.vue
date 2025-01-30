<template>
    <div>
        <div v-if="$page.props.flash && $page.props.flash.success && show" class="flex items-center justify-between max-w-full mb-8 bg-green-500 rounded">
            <div class="flex items-center">
                <svg class="w-4 h-4 ml-4 mr-2 flex-shrink-0 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><polygon points="0 11 2 9 7 14 18 3 20 5 7 18"/></svg>
                <div class="py-4 text-sm font-medium text-white">{{ $page.props.flash.success }}</div>
            </div>
            <button type="button" class="p-2 mr-2 group" @click="show = false"> 
                <svg class="block w-2 h-2 fill-green-800 group-hover:fill-white" xmlns="http://www.w3.org/2000/svg" width="235.908" height="235.908" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div v-if="$page.props.flash && ($page.props.flash.error || Object.keys($page.props.errors).length > 0) && show" class="flex items-center justify-between max-w-full mt-4 mb-8 bg-red-400 rounded-none">
            <div class="flex items-center">
                <svg class="w-4 h-4 ml-4 mr-2 flex-shrink-0 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                <div v-if="$page.props.flash.error" class="py-4 text-sm font-medium text-white">{{ $page.props.flash.error }}</div>
                <div v-else class="py-4 text-sm font-medium text-white">
                    <span v-if="Object.keys($page.props.errors).length === 1">Há um erro de formulário</span>
                    <span v-else>Existem {{ Object.keys($page.props.errors).length }} erros no formulário</span>
                </div>
            </div>   

            <button type="button" class="p-2 mr-2 group" @click="show = false"> 
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-red-800" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /> </svg>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            show: true,
        }
    },
    watch: {
        '$page.props.flash': {
            
            handler() {
                this.show = true
            },
            deep: true,
        }
    }
}
</script>