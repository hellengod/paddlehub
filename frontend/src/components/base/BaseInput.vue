<template>
    <div class="container">
        <label for="input"> {{ props.label }}</label>
        <input :type="props.type" :placeholder="props.placeholder" :value="props.modelValue">
    </div>
</template>
<script setup lang="ts">
interface BaseInputProps {
    label: string;
    type:string;
    placeholder: string;
    modelValue:string;
}

const props = defineProps < BaseInputProps > ();
defineEmits

</script>