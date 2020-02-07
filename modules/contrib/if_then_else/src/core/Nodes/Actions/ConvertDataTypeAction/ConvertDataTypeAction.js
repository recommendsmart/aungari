// Defining Vue controler for Condition node.
// create it using their own modules.
var VueConvertDataTypeActionControl = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData', 'onChange'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Field</label>
    <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select Data Type">
    </multiselect>      
  </div>
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.convert_data_type_action.type,
      class: drupalSettings.if_then_else.nodes.convert_data_type_action.class,
      name: drupalSettings.if_then_else.nodes.convert_data_type_action.name,
      options: [],
      data_type: [],
      value: [],
    }
  },
  methods: {
    fieldValueChanged(value){
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({name: value.name, code: value.code});
      this.onChange(value.code);
      this.putData('data_type',selectedOptions);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.convert_data_type_action.type);
    this.putData('class', drupalSettings.if_then_else.nodes.convert_data_type_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.convert_data_type_action.name);

    //setting values of selected data type option when rule edit page loads.
    var get_data_type = this.getData('data_type');
    if(typeof get_data_type != 'undefined'){
      this.value = get_data_type;
      this.onChange(get_data_type[0].code);

    }else{
      this.putData('data_type',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.convert_data_type_action.compare_options){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.convert_data_type_action.compare_options;
    }
  }
}

class ConvertDataTypeActionControl extends Rete.Control {

  constructor(emitter, key, onChange) {
    super(key);
    this.component = VueConvertDataTypeActionControl;
    this.props = { emitter, ikey: key, onChange };
  }
}

class ConvertDataTypeActionComponent extends Rete.Component {
  constructor(){
    var nodeName = 'convert_data_type_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_outputs = [];
    var nodeName = 'convert_data_type_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];

    node_outputs['success'] = new Rete.Output('success', 'Success', sockets['bool']);
    node_outputs['success']['description'] = node.outputs['success'].description;

    node_outputs['output'] = new Rete.Output('output', 'Output', sockets['string']);
    node_outputs['output']['description'] = node.outputs['output'].description;

    eventNode.addOutput(node_outputs['success']);
    eventNode.addOutput(node_outputs['output']);

    function handleInput(){
    	return function (value) {
        let socket_out = eventNode.outputs.get('output');
        if(value == 'int'){
          socket_out.socket = sockets['number'];
        }else if(value == 'str'){
          socket_out.socket = sockets['string'];
        }
        eventNode.outputs.set('output',socket_out);
        eventNode.update();
        editor.view.updateConnections({node: eventNode});
        editor.trigger('process');
      }
    }

    eventNode.addControl(new ConvertDataTypeActionControl(this.editor, nodeName,handleInput()));
    for (let name in node.inputs) {
      let inputLabel = node.inputs[name].label + (node.inputs[name].required ? ' *' : '');
      if (node.inputs[name].sockets.length === 1) {
        let  inputObject = new Rete.Input(name, inputLabel, sockets[node.inputs[name].sockets[0]]);
        inputObject['description'] = node.inputs[name].description;
        eventNode.addInput(inputObject);
      }
      else if (node.inputs[name].sockets.length > 1) {
        let socketNames = [];
        let socketLabels = [];
        for (let idx in node.inputs[name].sockets) {
          socketNames.push(node.inputs[name].sockets[idx]);
          socketLabels.push(sockets[node.inputs[name].sockets[idx]].name);
        }
        socketNames.sort();
        socketLabels.sort();
        let socketLabel = socketLabels.join(', ');
        let socketName = socketNames.join(', ');

        if (!(socketName in sockets)) {
          sockets[socketName] = new Rete.Socket(socketLabel);
        }

        for (let idx in node.inputs[name].sockets) {
          if (!sockets[node.inputs[name].sockets[idx]].compatibleWith(sockets[socketName])) {
            sockets[node.inputs[name].sockets[idx]].combineWith(sockets[socketName]);
            if (typeof compatibleSockets[node.inputs[name].sockets[idx]] === "undefined") {
              compatibleSockets[node.inputs[name].sockets[idx]] = [];
            }
            compatibleSockets[node.inputs[name].sockets[idx]].push(socketName);
          }
        }
        let inputObject = new Rete.Input(name, inputLabel, sockets[socketName]);
        inputObject['description'] =  node.inputs[name].description;
        eventNode.addInput(inputObject);
      }
    }
    eventNode['description'] = node.description;
  }
  worker(eventNode, inputs, outputs) {
    //outputs['form'] = eventNode.data.event;
  }
}
