var VueIsFieldValueEmptyControl = {
  props: ['getData', 'putData'],
  data(){
    return {
      class: drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.type,
      type: drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.type,
      name: drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.name
    }
  },
  template: '',
  mounted(){
    // initialize variable for data
    this.putData('type',drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.type);
    this.putData('class',drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.class);
    this.putData('name',drupalSettings.ifthenelserule.nodes.isFieldValueEmpty.name);
  }
}

class IsFieldEmptyControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueIsFieldValueEmptyControl;
  }
}

class IsFieldValueEmptyComponent extends Rete.Component {

  constructor(){
    super("Condition: Is Field Value Empty");
  }

  builder(node) {
    //creating input and output sockets
    var output = new Rete.Output('execute', "", conditionSocket);
    var formStateInput = new Rete.Input('form_state',"",formStateSocket);

    return node.addControl(new IsFieldEmptyControl(this.editor, 'formId')).addOutput(output).addInput(formStateInput);
  }

  worker(node, inputs, outputs) {
        
  }
}

var components = [new IsFieldValueEmptyComponent()];	

components.map(c => {
  editor.register(c);
  engine.register(c);
});