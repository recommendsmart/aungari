class EntityIsViewedEventControl extends Rete.Control {

  constructor(emitter, key, onChange) {
    super(key);
    this.component = {
      components: {
        // Component included for Multiselect.
        Multiselect: window.VueMultiselect.default
      },
      props: ['onChange', 'emitter', 'ikey', 'getData', 'putData'],
      template: `
    <div class="fields-container">
      <div class="label">Match Condition</div>

      <div class="radio">
        <input type="radio" :id="radio1_uid" value="list" v-model="selection" @change="selectionChanged">
        <label :for="radio1_uid">Select An Entity</label>
      </div>
      
      <div v-if="selection === 'list'">
        <div class="entity-select">
          <label class="typo__label">Entity</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
          placeholder="Entity" @input="entitySelected" label="label" 
          track-by="value"></multiselect></div><div class="bundle-select" v-if="showBundleList">
        
          <label class="typo__label">Bundle</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_bundle" :options="bundles" :show-labels="false" 
          placeholder="Bundle" @input="bundleSelected" label="label" 
          track-by="value"></multiselect>
        </div>
      </div>

      <div class="radio">
        <input type="radio" :id="radio2_uid" value="all" v-model="selection" @change="selectionChanged">
        <label :for="radio2_uid">All Entities</label>
      </div>
      
    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.entity_is_viewed_event.type,
          class: drupalSettings.if_then_else.nodes.entity_is_viewed_event.class,
          name: drupalSettings.if_then_else.nodes.entity_is_viewed_event.name,
          value: 0,
          showBundleList: true,
          entities: [],
          bundles: [],
          selection: 'list',
          selected_entity: [],
          selected_bundle: [],
          radio1_uid: '',
          radio2_uid: '',
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
          this.bundles = [];
          this.selected_bundle = [];
          this.bundleSelected();
          this.selected_entity = [];
          if (value !== undefined && value !== null && value !== '') { //check if an entity is selected
            let entity_id = value.value;
            this.selected_entity = {
              label: value.label,
              value: value.value
            };
            //This value is passed from module.
            let bundle_list = drupalSettings.if_then_else.nodes.entity_is_viewed_event.entity_info[entity_id]['bundles'];
            this.showBundleList = true;

            Object.keys(bundle_list).forEach(itemKey => {
              this.bundles.push({
                label: bundle_list[itemKey].label,
                value: bundle_list[itemKey].bundle_id
              });
            });
          } else {
            this.putData('selected_bundle', []);
          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_entity', this.selected_entity);
          editor.trigger('process');
        },
        bundleSelected() {
          //Triggered when a bundle is selected. We are fetching fields using ajax in this function
          this.showLoadingSpinner = false;

          this.putData('selected_bundle', this.selected_bundle);
          if (this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity.value !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
            this.onChange(this.selected_entity.value, this.selected_bundle.value);
          }
          editor.trigger('process');
        },
        selectionChanged() {
          this.putData('selection', this.selection);
          if (typeof this.selected_entity != 'undefined' && this.selected_entity.value !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
            this.onChange(this.selected_entity.value, this.selected_bundle.value, this.selection);
          } else {
            this.onChange('', '', this.selection);
          }
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.entity_is_viewed_event.type);
        this.putData('class', drupalSettings.if_then_else.nodes.entity_is_viewed_event.class);
        this.putData('name', drupalSettings.if_then_else.nodes.entity_is_viewed_event.name);

        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_entity = this.getData('selected_entity');
        this.selected_bundle = this.getData('selected_bundle');
        if (this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== '' && this.selected_bundle != undefined && typeof this.selected_bundle != 'undefined' && this.selected_bundle !== '') {
          this.onChange(this.selected_entity.value, this.selected_bundle.value);
        }
        this.selection = this.getData('selection');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
        this.radio1_uid = _.uniqueId('radio_');
        this.radio2_uid = _.uniqueId('radio_');

        //Fetching values of fields when editing rule page loads
        if (drupalSettings.if_then_else.nodes.entity_is_viewed_event.entity_info) {
          var entities_list = drupalSettings.if_then_else.nodes.entity_is_viewed_event.entity_info;
          Object.keys(entities_list).forEach(itemKey => {
            this.entities.push({
              label: entities_list[itemKey].label,
              value: entities_list[itemKey].entity_id
            });
          });

          // Load the bundle list when form loads for edit
          this.selected_entity = this.getData('selected_entity');
          if (this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== '') {
            let selected_entity = this.selected_entity.value;
            if (drupalSettings.if_then_else.nodes.entity_is_viewed_event.entity_info) {
              let bundle_list = drupalSettings.if_then_else.nodes.entity_is_viewed_event.entity_info[selected_entity]['bundles'];
              Object.keys(bundle_list).forEach(itemKey => {
                this.bundles.push({
                  label: bundle_list[itemKey].label,
                  value: bundle_list[itemKey].bundle_id
                });
              });
            }
          }
        }
      }
    };
    this.props = {
      emitter,
      ikey: key,
      onChange
    };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}

class EntityIsViewedEventComponent extends Rete.Component {
  constructor() {
    var nodeName = 'entity_is_viewed_event';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    super(jsUcfirst(node.type) + ": " + node.label);
  }

  //Event node builder
  builder(eventNode) {

    var node_outputs = [];
    var nodeName = 'entity_is_viewed_event';
    var node = drupalSettings.if_then_else.nodes[nodeName];
    node_outputs['success'] = new Rete.Output('success', 'Success', sockets['bool']);
    node_outputs['success']['description'] = node.outputs['success'].description;

    node_outputs['entity'] = new Rete.Output('entity', 'Entity', sockets['object.entity']);
    node_outputs['entity']['description'] = node.outputs['entity'].description;

    eventNode.addOutput(node_outputs['success']);
    eventNode.addOutput(node_outputs['entity']);

    function handleInput() {
      return function(entityValue, bundleValue, selection) {
        let socket_out = eventNode.outputs.get('entity');
        if (selection == 'all') {
          socket_out.socket = sockets['object.entity'];
          eventNode.outputs.set('entity', socket_out);
        } else {
          if (entityValue != undefined && typeof entityValue != 'undefined' && entityValue !== '' && bundleValue != undefined && typeof bundleValue != 'undefined' && bundleValue !== '') {
            socket_out.socket = sockets['object.entity.' + entityValue + '.' + bundleValue];
            makeCompatibleSocketsByName('object.entity.' + entityValue + '.' + bundleValue);
  
            eventNode.outputs.set('entity', socket_out);            
          }
        }
        eventNode.update();
        editor.view.updateConnections({
          node: eventNode
        });
        editor.trigger('process');
      }
    }

    eventNode.addControl(new EntityIsViewedEventControl(this.editor, nodeName, handleInput()));
    eventNode['description'] = node.description;

  }
  worker(eventNode, inputs, outputs) {
    //outputs['form'] = eventNode.data.event;
  }
}
