// New add action for setting default values of fields
var VueUserHasEntityFieldAccessConditionControl = {
  components: {
    // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['getData', 'putData', 'emitter'],
  provide() {
    return {
      emitter: this.emitter
    }
  },
  data(){
    return{
      type: drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.type,
      class: drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.class,
      name: drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.name,
      classArg: drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.classArg,
      options: [],
      opt_options: [],
      form_fields: [],
      opt_form_fields: [],
      value: [],
      opt_value: [],
    }
  },
  template: `<div class="fields-container">
    <div class="entity-select">
      <label class="typo__label">Field</label>
      <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select a field">
      </multiselect>      
    </div>
    <div class="operation-select">
      <label class="typo__label">Operation</label>
      <multiselect @wheel.native.stop="wheel" v-model="opt_value" :options="opt_options" @input="optFieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select Operation Access">
      </multiselect>      
    </div>    
  </div>`,

  methods: {
    fieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});
      
      this.putData('form_fields',selectedOptions);
      editor.trigger('process');
    },
    optFieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});

      this.putData('opt_form_fields',selectedOptions);
      editor.trigger('process');
    },
  },

  mounted(){
    // initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.type);
    this.putData('class',drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.classArg);
    
    //setting values of selected fields when rule edit page loads.
    var get_form_fields = this.getData('form_fields');
    if(typeof get_form_fields != 'undefined'){
      this.value = this.getData('form_fields');
    }else{
      this.putData('form_fields',[]);
    }

    //setting values of selected fields for operation field when rule edit page loads.
    var get_opt_form_fields = this.getData('opt_form_fields');
    if(typeof get_opt_form_fields != 'undefined'){
      this.opt_value = this.getData('opt_form_fields');
    }else{
      this.putData('opt_form_fields',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.form_fields){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.form_fields;
      this.opt_options = drupalSettings.if_then_else.nodes.user_has_entity_field_access_condition.opt_options;
    }
  }
};

class UserHasEntityFieldAccessConditionControl extends Rete.Control {
  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueUserHasEntityFieldAccessConditionControl;
    this.props = { emitter, ikey: key, readonly };
  }
}
