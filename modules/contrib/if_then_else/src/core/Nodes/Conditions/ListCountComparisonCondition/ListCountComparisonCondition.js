// Defining Vue controler for List Count Comparison Action node.
// create it using their own modules.
var VueListCountComparisonConditionControl = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Operator</label>
    <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select Operator">
    </multiselect>      
  </div>
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.list_count_comparison_condition.type,
      class: drupalSettings.if_then_else.nodes.list_count_comparison_condition.class,
      name: drupalSettings.if_then_else.nodes.list_count_comparison_condition.name,
      options: [],
      operator: [],
      value: [],
    }
  },
  methods: {
    fieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});

      this.putData('operator',selectedOptions);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.list_count_comparison_condition.type);
    this.putData('class', drupalSettings.if_then_else.nodes.list_count_comparison_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.list_count_comparison_condition.name);

    //setting values of selected operator option when rule edit page loads.
    var get_operator = this.getData('operator');
    if(typeof get_operator != 'undefined'){
      this.value = this.getData('operator');
    }else{
      this.putData('operator',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.list_count_comparison_condition.operator_options){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.list_count_comparison_condition.operator_options;
    }
  }
}

class ListCountComparisonConditionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueListCountComparisonConditionControl;
    this.props = { emitter, ikey: key, readonly };
  }
}
