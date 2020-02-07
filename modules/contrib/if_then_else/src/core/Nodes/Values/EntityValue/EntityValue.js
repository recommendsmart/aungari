var VueEntityValueControl = {
  components: {
    // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['ikey', 'getData', 'putData', 'emitter', 'onChange'],
  template: `<div class="fields-container">
    <div class="entity-select">
      <label class="typo__label">Select an Entity</label>
      <multiselect @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
      placeholder="Entity" @input="entitySelected" label="label" 
      track-by="value"></multiselect>
    </div>
           
    <label>Entity</label>
    <div class="radio">
      <input type="radio" :id="radio1_uid" value="value" v-model="input_selection" @change="inputSelectionChanged">
      <label :for="radio1_uid">Enter Entity Id Below</label>
    </div>
    <input v-if="input_selection == 'value'" type="text" class="input" name="entity-id-class" :value="entityId" 
    @blur="updateEntityId" />
    <div class="radio">
      <input type="radio" :id="radio2_uid" value="input" v-model="input_selection" @change="inputSelectionChanged">
      <label :for="radio2_uid">Select From Entity Id</label>
    </div>
                
  </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.entity_value.type,
      class: drupalSettings.if_then_else.nodes.entity_value.class,
      name: drupalSettings.if_then_else.nodes.entity_value.name,
      classArg: drupalSettings.if_then_else.nodes.entity_value.classArg,
      selected_entity: [],
      entityId: '',
      entities: [],
      input_selection: 'value',
    }
  },
  methods: {
    entitySelected(value) {
      //Triggered when selecting an entity from entity dropdown.
      //reinitialize all values
      this.entityId = '';
      this.selected_entity = [];
      if (value !== null) { //check if an entity is selected
        this.selected_entity = {
          label: value.label,
          value: value.value
        };
      }

      //Updating reactive variable of Vue to reflect changes on frontend
      this.onChange(value.value);
      this.putData('selected_entity', this.selected_entity);
      this.putData('entityId', this.entityId);
      editor.trigger('process');
    },
    updateEntityId(e) {
      //Triggered when entering form class
      this.putData('entityId', e.target.value);
      editor.trigger('process');
    },
    inputSelectionChanged() {
      this.putData('input_selection', this.input_selection);
      editor.trigger('process');
    }
  },

  mounted() {
    this.putData('type', drupalSettings.if_then_else.nodes.entity_value.type);
    this.putData('class', drupalSettings.if_then_else.nodes.entity_value.class);
    this.putData('name', drupalSettings.if_then_else.nodes.entity_value.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.entity_value.classArg);
    
    this.input_selection = this.getData('input_selection');

    //Setting values of retejs condition nodes when editing rule page loads
    var selected_entity = this.getData('selected_entity');
    if (typeof selected_entity != 'undefined') {
      this.selected_entity = selected_entity;
      this.onChange(selected_entity.value);
    } else {
      this.putData('selected_entity', []);
    }
    this.entityId = this.getData('entityId');
  },
  created() {
    //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
    this.radio1_uid = _.uniqueId('radio_');
    this.radio2_uid = _.uniqueId('radio_');

    //Fetching values of fields when editing rule page loads
    if (drupalSettings.if_then_else.nodes.form_class_condition.entity_info) {
      var entities_list = drupalSettings.if_then_else.nodes.form_class_condition.entity_info;
      Object.keys(entities_list).forEach(itemKey => {
        this.entities.push({
          label: entities_list[itemKey].label,
          value: entities_list[itemKey].entity_id
        });
      });
    }
  }

}

class EntityValueControl extends Rete.Control {

  constructor(emitter, key, onChange) {
    super(key);
    this.component = VueEntityValueControl;
    this.props = {
      emitter,
      ikey: key,
      onChange
    };
  }
}

class EntityValueComponent extends Rete.Component {
  constructor() {
    var nodeName = 'entity_value';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_outputs = [];
    var nodeName = 'entity_value';
    var node = drupalSettings.if_then_else.nodes[nodeName];

    node_outputs['entity'] = new Rete.Output('entity', 'Entity', sockets['object.entity']);
    node_outputs['entity']['description'] = node.outputs['entity'].description;

    eventNode.addOutput(node_outputs['entity']);

    var nodeName = 'entity_value';
    var node = drupalSettings.if_then_else.nodes[nodeName];

    function handleInput() {
      return function(value) {
        let socket_out = eventNode.outputs.get('entity');
        if (value != undefined && typeof value != 'undefined' && value !== '') {
          socket_out.socket = sockets['object.entity.' + value ];
          makeCompatibleSocketsByName('object.entity.' + value );
        }
        eventNode.outputs.set('entity', socket_out);
        eventNode.update();
        editor.view.updateConnections({
          node: eventNode
        });
        editor.trigger('process');
      }
    }

    eventNode.addControl(new EntityValueControl(this.editor, nodeName, handleInput()));
    for (let name in node.inputs) {
      let inputLabel = node.inputs[name].label + (node.inputs[name].required ? ' *' : '');
      if (node.inputs[name].sockets.length === 1) {
        let  inputObject = new Rete.Input(name, inputLabel, sockets[node.inputs[name].sockets[0]]);
        inputObject['description'] = node.inputs[name].description;
        eventNode.addInput(inputObject);
      }
    }
    eventNode['description'] = node.description;

  }
  worker(eventNode, inputs, outputs) {
    //outputs['form'] = eventNode.data.event;
  }
}
