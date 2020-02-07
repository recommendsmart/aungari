// Defining Vue controler for Condition node.
// create it using their own modules.
var VueAddItemListAction = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Position</label>
    <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select position">
    </multiselect>
    <div class="radio">
        <input type="checkbox" value="case_sensitive" v-model="selection" @change="selectionChanged">
        <label>Whether or not we can add duplicate items.</label>
      </div>   
  </div>
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.add_item_list_action.type,
      class: drupalSettings.if_then_else.nodes.add_item_list_action.class,
      name: drupalSettings.if_then_else.nodes.add_item_list_action.name,
      options: [],
      postion: [],
      value: [],
      selection: '',
    }
  },
  methods: {
    fieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});
      
      this.putData('postion',selectedOptions);
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
    this.putData('type', drupalSettings.if_then_else.nodes.add_item_list_action.type);
    this.putData('class', drupalSettings.if_then_else.nodes.add_item_list_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.add_item_list_action.name);

    //setting values of selected compare option when rule edit page loads.
    var get_postion = this.getData('postion');
    if(typeof get_postion != 'undefined'){
      this.value = this.getData('postion');
    }else{
      this.putData('postion',[]);
    }

    this.selection = this.getData('selection');
  },
  created() {
    if(drupalSettings.if_then_else.nodes.add_item_list_action.compare_options){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.add_item_list_action.compare_options;
    }
  }
}

class AddItemListActionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueAddItemListAction;
    this.props = { emitter, ikey: key, readonly };
  }
}
