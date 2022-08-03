<script setup>
import { nextTick, ref } from 'vue';
import Button from '../Button.vue'
import Icon from '@/Components/Icon.vue'

const open = ref(false)
</script>

<template>
  <div @mouseleave="open = false" class="relative">
    <Button @click.prevent="open = ! open" class="bg-gray-800 hover:bg-gray-700 active:bg-gray-900">
      <Icon name="caret-down" />
      <p class="uppercase font-semibold">action</p>
    </Button>

    <Transition
      enterActiveClass="transition-all duration-300"
      leaveActiveClass="transition-all duration-300"
      enterFromClass="opacity-0 translate-x-full"
      leaveToClass="opacity-0 translate-x-full">
      <div v-if="open" class="absolute right-0 flex flex-col max-w-[10rem] bg-gray-200 dark:bg-gray-600 rounded-md shadow-xl z-10">
        <div class="p-1">
          <slot />
        </div>
      </div>
    </Transition>
  </div>
</template>