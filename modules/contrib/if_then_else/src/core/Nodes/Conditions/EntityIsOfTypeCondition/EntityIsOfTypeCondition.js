class EntityIsOfTypeConditionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = {
      components: {
        // Component included for Multiselect.
        Multiselect: window.VueMultiselect.default
      },
      props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
      template: `
        <div class="fields-container">
          <div class="entity-select">
            <label class="typo__label">Type</label>
            <multiselect  @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
            placeholder="Entity" @input="entitySelected" label="label" 
            track-by="value"></multiselect>
          </div>
        </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.entity_is_of_type_condition.type,
          class: drupalSettings.if_then_else.nodes.entity_is_of_type_condition.class,
          name: drupalSettings.if_then_else.nodes.entity_is_of_type_condition.name,
          classArg: drupalSettings.if_then_else.nodes.entity_is_of_type_condition.classArg,
          value: 0,
          entities: [],
          selected_entity: [],
        }
      },
      methods: {
        update() {
          //Triggered on focus out of formclass input field
          if (this.ikey)
            this.putData(this.ikey, this.value);

          //This is called to reprocess the retejs editor
          editor.trigger('process');
        },
        entitySelected(value) {
          //Triggered when selecting an entity from entity dropdown.
          //reinitialize all values
          this.selected_entity = [];
          if (value !== null) { //check if an entity is selected
            let entity_id = value.value;
            this.selected_entity = {
              label: value.label,
              value: value.value
            };

          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_entity', this.selected_entity);
          editor.trigger('process');
        },
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.entity_is_of_type_condition.type);
        this.putData('class', drupalSettings.if_then_else.nodes.entity_is_of_type_condition.class);
        this.putData('name', drupalSettings.if_then_else.nodes.entity_is_of_type_condition.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.entity_is_of_type_condition.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_entity = this.getData('selected_entity');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs

        //Fetching values of fields when editing rule page loads
        if (drupalSettings.if_then_else.nodes.entity_is_of_type_condition.entity_info) {
          var entities_list = drupalSettings.if_then_else.nodes.entity_is_of_type_condition.entity_info;
          Object.keys(entities_list).forEach(itemKey => {
            this.entities.push({
              label: entities_list[itemKey].label,
              value: entities_list[itemKey].entity_id
            });
          });
        }
      }
    };
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}
