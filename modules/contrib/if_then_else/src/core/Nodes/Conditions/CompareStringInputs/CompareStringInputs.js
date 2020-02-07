// Defining Vue controler for Condition node.
// create it using their own modules.
var VueCompareStringInputs = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Field</label>
    <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select compare operation">
    </multiselect>
    <div class="radio">
        <input type="checkbox" value="case_sensitive" v-model="selection" @change="selectionChanged">
        <label>Enable case sensitive comparision.</label>
      </div>   
  </div>
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.compare_string_inputs.type,
      class: drupalSettings.if_then_else.nodes.compare_string_inputs.class,
      name: drupalSettings.if_then_else.nodes.compare_string_inputs.name,
      options: [],
      compare_type: [],
      value: [],
      selection: '',
    }
  },
  methods: {
    fieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});
      
      this.putData('compare_type',selectedOptions);
      editor.trigger('process');
    },
    selectionChanged(value){
      this.putData('selection', this.selection);
      editor.trigger('process');
    }
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.compare_string_inputs.type);
    this.putData('class', drupalSettings.if_then_else.nodes.compare_string_inputs.class);
    this.putData('name', drupalSettings.if_then_else.nodes.compare_string_inputs.name);

    //setting values of selected compare option when rule edit page loads.
    var get_compare_type = this.getData('compare_type');
    if(typeof get_compare_type != 'undefined'){
      this.value = this.getData('compare_type');
    }else{
      this.putData('compare_type',[]);
    }

    this.selection = this.getData('selection');
  },
  created() {
    if(drupalSettings.if_then_else.nodes.compare_string_inputs.compare_options){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.compare_string_inputs.compare_options;
    }
  }
}

class CompareStringInputsControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueCompareStringInputs;
    this.props = { emitter, ikey: key, readonly };
  }
}
