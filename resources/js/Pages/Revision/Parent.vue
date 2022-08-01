<script setup>
import { getCurrentInstance, ref } from 'vue';

const self = getCurrentInstance()
const { procedure } = defineProps({
  procedure: Object,
})
</script>

<template>
  <div class="flex flex-col space-y-4">
    <div class="flex items-center space-x-2 text-xl font-bold">
      <h1>{{ procedure.position }}.</h1>
      <h1>{{ procedure.name }}</h1>
    </div>

    <div class="flex flex-col space-y-2 ml-8">
      <slot />
    </div>
  </div>
</template>