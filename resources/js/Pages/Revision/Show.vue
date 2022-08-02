<script setup>
import { getCurrentInstance, ref, onMounted } from 'vue'
import { Head } from '@inertiajs/inertia-vue3'
import Previewer from './Previewer.vue'
import logo from './logo.png'

const self = getCurrentInstance()
const { document, revision, procedures } = defineProps({
  document: Object,
  revision: Object,
  procedures: Array,
})
</script>

<template>
  <Head :title="`${document.name.toUpperCase()} - ${revision.code.toUpperCase()}`" />

  <table class="w-full border-collapse">
    <thead>
      <tr class="border print:border-2 border-gray-300 p-4">
        <th class="border print:border-2 border-inherit text-center capitalize whitespace-nowrap w-1/4 px-4">
          <img :src="logo" alt="logo" class="w-full object-contain object-center" />
        </th>
        <th class="border print:border-2 border-inherit text-center capitalize font-bold whitespace-nowrap w-2/4 p-4">
          <h1 class="text-5xl">
            Document {{ document.name }}
          </h1>
        </th>
        <th class="border print:border-2 border-inherit text-center capitalize font-bold whitespace-nowrap w-1/4 p-4">
          <table class="w-full border-collapse">
            <tr>
              <th class="text-md text-left font-bold uppercase">{{ __('revision code') }}</th>
              <td class="text-sm text-left font-semibold uppercase">{{ revision.code }}</td>
            </tr>

            <tr>
              <th class="text-md text-left font-bold uppercase">{{ __('valid until') }}</th>
              <td class="text-sm text-left font-semibold uppercase">{{ new Date(revision.expired_at).toLocaleDateString() }}</td>
            </tr>
          </table>
        </th>
      </tr>
    </thead>

    <tbody class="h-full">
      <tr class="border print:border-2 border-gray-300">
        <td class="border print:border-2 border-inherit p-4" colspan="1000">
          <Previewer :procedures="procedures" />
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style>
@page {
  size: auto;
  margin: 0;
}

@page :footer {
  display: none;
}

@page :header {
  display: none;
}
</style>