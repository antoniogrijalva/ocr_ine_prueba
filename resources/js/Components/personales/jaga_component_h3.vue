<script setup>
defineProps({
    text: {
        type: String,
        required: true
    }
});
</script>

<template>
    <h3 class="inline-block  text-lg font-extrabold text-slate-500 border-l-4  border-slate-900 pl-2 mb-2 rounded-md ">
        {{ text }}
    </h3>
    <!-- <h3 class="inline-block  text-lg font-medium text-indigo-300 border-l-4  border-indigo-500 pl-2 mb-2 rounded-md ">
        {{ text }}
    </h3> -->
</template>