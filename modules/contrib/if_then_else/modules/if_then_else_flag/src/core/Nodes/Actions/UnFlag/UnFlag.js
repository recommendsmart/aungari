var VueUnFlagActionControl = {
  props: ['emitter', 'ikey', 'getData', 'putData', 'onChange'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.un_flag_action.type,
      class: drupalSettings.if_then_else.nodes.un_flag_action.class,
      name: drupalSettings.if_then_else.nodes.un_flag_action.name,
      classArg: drupalSettings.if_then_else.nodes.un_flag_action.classArg,
      value: [],
      options: [],
      selected_options: [],
      flag_bundles: [],
      selected_bundles: [],
    }
  },
  template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
      <label class="typo__label">Flag</label>
      <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="entityValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select a flag type">
      </multiselect>
      <label v-if="value != ''" v-model="selected_bundles" class="typo__label">Flag</label>
      <multiselect @wheel.native.stop="wheel" v-if="value != ''" v-model="selected_bundles" :options="flag_bundles" :multiple="false" :taggable="true" @input="bundleValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select flag">
      </multiselect>
      </div>
    </div>`,
 
  methods: {
    entityValueChanged(value){
      if(value !== undefined && value !== null && value !== ''){
        //Triggered when selecting an field.
        var selectedOptions = [];

        selectedOptions = {name: value.name, code: value.code};

        //check if selected field value is changed.
        var prevSelectedField = this.getData('selected_options');
        if(typeof prevSelectedField != 'undefined' && prevSelectedField.code != value.code){
          this.selected_bundles = '';
          this.putData('selected_bundles','');
        }

        var field_roles = drupalSettings.if_then_else.nodes.un_flag_action.flag_bundles
        this.flag_bundles = field_roles[value.code];
        this.putData('selected_options',selectedOptions);
        editor.trigger('process');
      }else{
        this.putData('selected_options','');
        this.putData('selected_bundles','');
        this.value = '';
      }
    },
    bundleValueChanged(value){
      if(value !== undefined && value !== null && value !== ''){
        var selected_bundles = [];
        selected_bundles = {name: value.name, code: value.code};
        this.putData('selected_bundles',selected_bundles);
        selected_entity = this.getData('selected_options');
        this.onChange(value.entity_id, value.code);
        editor.trigger('process');
      }
    },
  },
  mounted() {
    //initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.un_flag_action.type);
    this.putData('class',drupalSettings.if_then_else.nodes.un_flag_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.un_flag_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.un_flag_action.classArg);
  
    //setting values of selected fields when rule edit page loads.
    //Setting values of retejs condition nodes when editing rule page loads
    var get_selected_options = this.getData('selected_options'); 
    var get_selected_bundles = this.getData('selected_bundles');
    if(typeof get_selected_options != 'undefined'){
      this.value = get_selected_options;
      var field_roles = drupalSettings.if_then_else.nodes.un_flag_action.flag_bundles
      this.flag_bundles = field_roles[get_selected_options.code];
      this.selected_bundles = this.getData('selected_bundles');
    }
    else {
      this.putData('selected_options',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.un_flag_action.flag_types){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.un_flag_action.flag_types;
    }
  } 
};
class UnFlagActionControl extends Rete.Control {
  constructor(emitter, key, onChange) {
    super(key);
    this.component = VueUnFlagActionControl;
    this.props = { emitter, ikey: key, onChange};
  }
}
class UnFlagActionComponent extends Rete.Component {
  constructor() {
    var nodeName = 'un_flag_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_inputs = [];
    var nodeName = 'un_flag_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    node_inputs['execute'] = new Rete.Input('execute', 'Execute', sockets['bool']);
    node_inputs['execute']['description'] = node.inputs['execute'].description;

    node_inputs['entity'] = new Rete.Input('entity', 'Entity', sockets['object.entity']);
    node_inputs['entity']['description'] = node.inputs['entity'].description;

    eventNode.addInput(node_inputs['execute']);
    eventNode.addInput(node_inputs['entity']);


    function handleInput() {
      return function(entity, bundle) {
        let socket_in = eventNode.inputs.get('entity');

        let new_socket = 'object.entity.'+entity+'.'+bundle;
        socket_in.socket = sockets[new_socket];
        
        eventNode.inputs.set('entity',socket_in);
        eventNode.update();
        editor.view.updateConnections({node: eventNode});
        editor.trigger('process');
      }
    }

    eventNode.addControl(new UnFlagActionControl(this.editor, nodeName, handleInput()));
    for (let name in node.outputs) {
      let outputObject = new Rete.Output(name, node.outputs[name].label, sockets[node.outputs[name].socket]);
      outputObject['description'] = node.outputs[name].description;
      eventNode.addOutput(outputObject);
    }
    eventNode['description'] = node.description;
  }
  worker(eventNode, inputs, outputs) {
    //outputs['form'] = eventNode.data.event;
  }
}