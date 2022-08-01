<script>
import { defineComponent, h } from "vue"
import Parent from './Parent.vue'
import Child from './Child.vue'

export default defineComponent({
  props: {
    procedures: Array,
  },

  setup(props, attrs) {
    return props => {
      const { procedures } = props

      const generate = (procedure) => {
        if (procedure.childs?.length) {
          return h(Parent, {
            ...attrs,
            procedure,
          }, procedure.childs.map(child => generate(child)))
        }

        return h(Child, {
          ...attrs,
          procedure,
        })
      }

      return h('div', {
        class: 'flex flex-col space-y-2',
      }, procedures.map(procedur => generate(procedur)))
    }
  },
})
</script>

<style>
figure {
  @apply mx-auto;
}

table {
  @apply w-full;
}

blockquote {
  @apply border-l-4 border-gray-300 pl-2 italic;
}
</style>