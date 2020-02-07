var VueGetEntityFieldActionControl = {
  props: ['getData', 'putData', 'emitter', 'onChange'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.get_entity_field_action.type,
      class: drupalSettings.if_then_else.nodes.get_entity_field_action.class,
      name: drupalSettings.if_then_else.nodes.get_entity_field_action.name,
      classArg: drupalSettings.if_then_else.nodes.get_entity_field_action.classArg,
      options: [],
      form_fields: [],
      field_entities: [],
      selected_entity: '',
      field_bundles: [],
      selected_bundle: '',
      field_type: '',
      field_cardinality: 1,
      value: [],
    }
  },
  template: `<div class="fields-container">
    <div class="entity-select">
      <label class="typo__label">Field</label>
      <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select a field">
      </multiselect>
      <label v-if="value != ''" v-model="selected_entity" class="typo__label">Entity</label>
      <multiselect @wheel.native.stop="wheel" v-if="value != ''" v-model="selected_entity" :options="field_entities" @input="entityFieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select an Entity">
      </multiselect>
      <label v-if="value != '' && selected_entity" class="typo__label">Bundle</label>
      <multiselect @wheel.native.stop="wheel" v-if="value != '' && selected_entity" v-model="selected_bundle" :options="field_bundles" @input="bundleFieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select a Bundle">
      </multiselect>  
    </div>
  </div>`,
  methods: {
    fieldValueChanged(value) {
      if (value !== undefined && value !== null && value !== '') {
        //Triggered when selecting an field.
        var selectedOptions = [];

        selectedOptions = {
          name: value.name,
          code: value.code
        };

        //check if selected field value is changed.
        var prevSelectedField = this.getData('form_fields');
        if (typeof prevSelectedField != 'undefined' && prevSelectedField.code != value.code) {
          this.selected_entity = '';
          this.selected_bundle = '';
          this.putData('selected_entity', '');
          this.putData('selected_bundle', '');
        }

        var field_entity = drupalSettings.if_then_else.nodes.get_entity_field_action.field_entity_bundle;
        this.field_entities = field_entity[value.code]['entity'];
        this.putData('form_fields', selectedOptions);
        editor.trigger('process');
      } else {
        this.putData('form_fields', '');
        this.putData('selected_entity', '');
        this.putData('selected_bundle', '');
        this.value = '';
        this.field_bundles = [];
      }
    },
    entityFieldValueChanged(value) {
      if (value !== undefined && value !== null && value !== '') {
        var selectedentity = [];
        selectedentity = {
          name: value.name,
          code: value.code
        };

        //check if selected entity value is changed.
        prevSelectedEntity = this.getData('selected_entity');
        if (typeof prevSelectedEntity != 'undefined' && prevSelectedEntity.code != value.code) {
          this.selected_bundle = '';
          this.putData('selected_bundle', '');
        }
        //Triggered when selecting an field.
        var field_entity = drupalSettings.if_then_else.nodes.get_entity_field_action.field_entity_bundle;
        this.field_bundles = field_entity[this.value.code][value.code]['bundle'];
        var field_cardinality = drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields_cardinality[selectedentity.code][this.value.code];
	      this.putData('field_cardinality', field_cardinality);
        this.putData('selected_entity', selectedentity);
        var field_type = drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields_type[selectedentity.code][this.value.code];
        this.putData('field_type', field_type);
        editor.trigger('process');
      } else {
        this.field_bundles = [];
        this.selected_bundle = '';
        this.putData('selected_entity', '');
        this.putData('selected_bundle', '');
      }
    },
    bundleFieldValueChanged(value) {
      var selectedbundle = [];
      selectedbundle = {
        name: value.name,
        code: value.code
      };
      this.putData('selected_bundle', selectedbundle);
      if (this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity.value !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
        var field_cardinality = drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields_cardinality[this.selected_entity.code][this.value.code];
        this.onChange(this.getData('field_type'), this.selected_entity.code, this.selected_bundle.code, field_cardinality);
      }
      editor.trigger('process');
    }
  },
  mounted() {
    // initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.get_entity_field_action.type);
    this.putData('class', drupalSettings.if_then_else.nodes.get_entity_field_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.get_entity_field_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.get_entity_field_action.classArg);
    
    //setting values of selected fields when rule edit page loads.
    var get_form_fields = this.getData('form_fields');
    if (typeof get_form_fields != 'undefined') {
      this.value = get_form_fields;

      var field_entity = drupalSettings.if_then_else.nodes.get_entity_field_action.field_entity_bundle;

      //setting value for selected entity
      var get_selected_entity = this.getData('selected_entity');
      if (typeof get_selected_entity != 'undefined') {
        //setting entity list
        this.field_entities = field_entity[get_form_fields.code]['entity'];
        this.selected_entity = get_selected_entity;

        var field_type = this.getData('field_type');
        //setting value for selected bundle
        var get_selected_bundle = this.getData('selected_bundle');
        if (typeof get_selected_bundle != 'undefined') {
          //setting bundle list
          this.field_bundles = field_entity[get_form_fields.code][this.selected_entity.code]['bundle'];
          this.selected_bundle = get_selected_bundle;
          if (typeof field_type != 'undefined') {
            var field_cardinality = drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields_cardinality[this.selected_entity.code][this.value.code];		
	          this.putData('field_cardinality', field_cardinality);
            this.onChange(field_type, this.selected_entity.code, this.selected_bundle.code, field_cardinality);
          }

        }
      }
    } else {
      this.putData('form_fields', []);
    }
  },
  created() {
    if (drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields) {
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.get_entity_field_action.form_fields;
    }
  }
}

class GetEntityFieldActionControl extends Rete.Control {
  constructor(emitter, key, onChange) {
    super(key);
    this.component = VueGetEntityFieldActionControl;
    this.props = {
      emitter,
      ikey: key,
      onChange,
    };
  }
}

class GetEntityFieldActionComponent extends Rete.Component {
  constructor() {
    var nodeName = 'get_entity_field_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_outputs = [];
    var nodeName = 'get_entity_field_action';
    var node = drupalSettings.if_then_else.nodes[nodeName];

    node_outputs['success'] = new Rete.Output('success', 'Success', sockets['bool']);
    node_outputs['success']['description'] = node.outputs['success'].description;

    node_outputs['field_value'] = new Rete.Output('field_value', 'Field Value', sockets['string']);
    node_outputs['field_value']['description'] = node.outputs['field_value'].description;

    eventNode.addOutput(node_outputs['success']);
    eventNode.addOutput(node_outputs['field_value']);


    function handleInput() {
      return function(value, entityValue = null, bundleValue = null, field_cardinality = 1) {
        let socket_out = eventNode.outputs.get('field_value');
        let socket_in = eventNode.inputs.get('entity');

        if(typeof window[value+'_type_field_socket'] == 'function'){
          socket_out.socket = window[value+'_type_field_socket'](field_cardinality);
        }

        if (entityValue != undefined && typeof entityValue != 'undefined' && entityValue !== '' && bundleValue != undefined && typeof bundleValue != 'undefined' && bundleValue !== '') {
          socket_in.socket = sockets['object.entity.' + entityValue + '.' + bundleValue];
          makeCompatibleSocketsByName('object.entity.' + entityValue + '.' + bundleValue);
        }

        eventNode.inputs.set('entity', socket_in);
        eventNode.outputs.set('field_value', socket_out);
        eventNode.update();
        editor.view.updateConnections({
          node: eventNode
        });
        editor.trigger('process');
      }
    }

    eventNode.addControl(new GetEntityFieldActionControl(this.editor, nodeName, handleInput()));
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

function email_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['string'];
  } else {
    makeCompatibleSocketsByName('array.string');
    return sockets['array.string'];
  }
}

function list_string_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['string'];
  } else {
    makeCompatibleSocketsByName('array.string');
    return sockets['array.string'];
  }
}

function datetime_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['string'];
  } else {
    makeCompatibleSocketsByName('array.string');
    return sockets['array.string'];
  }
}

function string_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['string'];
  } else {
    makeCompatibleSocketsByName('array.string');
    return sockets['array.string'];
  }
}

function string_long_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['string'];
  } else {
    makeCompatibleSocketsByName('array.string');
    return sockets['array.string'];
  }
}

function entity_reference_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function list_integer_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function list_float_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function decimal_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function float_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function integer_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    return sockets['number'];
  } else {
    makeCompatibleSocketsByName('array.number');
    return sockets['array.number'];
  }
}

function boolean_type_field_socket(field_cardinality){
  if (field_cardinality == 1){
    return sockets['bool'];
  } else {
    makeCompatibleSocketsByName('array.bool');
    return sockets['array.bool'];
  }
}

function text_with_summary_type_field_socket(field_cardinality){
  if (field_cardinality == 1){
    makeCompatibleSocketsByName('object.field.text_with_summary');
    return sockets['object.field.text_with_summary'];
  } else {
    makeCompatibleSocketsByName('array.object.field.text_with_summary');
    return sockets['array.object.field.text_with_summary'];
  }
}

function image_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    makeCompatibleSocketsByName('object.field.image');
    return sockets['object.field.image'];
  } else {
    makeCompatibleSocketsByName('array.object.field.image');
    return sockets['array.object.field.image'];
  }
}

function link_type_field_socket(field_cardinality){
  if (field_cardinality == 1) {
    makeCompatibleSocketsByName('object.field.link');
    return sockets['object.field.link'];
  } else {
    makeCompatibleSocketsByName('array.object.field.link');
    return sockets['array.object.field.link'];
  }
}

function text_type_field_socket(field_cardinality){
  makeCompatibleSocketsByName('object.field.text_long');
  return sockets['object.field.text_long'];
}

function text_long_type_field_socket(field_cardinality){
  makeCompatibleSocketsByName('object.field.text_long');
  return sockets['object.field.text_long'];
}