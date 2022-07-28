<script>
import { Inertia } from "@inertiajs/inertia"
import { useForm } from "@inertiajs/inertia-vue3"
import { defineComponent, getCurrentInstance, h } from "vue"
import Child from './Child.vue'
import Parent from './Parent.vue'

export default defineComponent({
  props: {
    procedures: Array,
    refresh: Function,
    edit: Function,
  },

  data: () => ({
    procedureOnDrag: null,
  }),

  setup(props, attrs) {
    return props => {
      const self = getCurrentInstance()
      const { procedures, refresh, edit } = props

      const drag = procedure => {
        self.procedureOnDrag = procedure
      }

      const drop = drop => {
        const drag = self.procedureOnDrag

        if (!drag)
          return
        
        if (drag.parent_id != drop.parent_id) {
          return
        }

        if (drag.position === drop.position) {
          return
        }
        
        useForm({
          drag: drag.id,
          drop: drop.id,
        }).patch(route('procedure.drill'))
      }

      const generate = procedure => {
        if (procedure.childs?.length) {
          return h(Parent, {
            ...attrs,
            procedure,
            childs: procedure.childs,
            refresh,
            drag,
            drop,
            edit,
          }, procedure.childs.map(child => generate(child)))
        }

        return h(Child, { ...attrs, procedure, refresh, drag, drop, edit, })
      }

      return h('div', {
        ...attrs,
        class: 'flex flex-col space-y-1',
      }, procedures.map(procedure => generate(procedure)))
    }
  },
})
</script>