class PageRedirectActionControl extends Rete.Control {

    constructor(emitter, key, readonly) {
        super(key);
        this.component = {
            props: ['ikey', 'getData', 'putData', 'emitter'],
            template: `
            <div class="field-container">
              <label>Path to redirect to</label>
              <div class="radio">
                <input type="radio" :id="radio1_uid" value="value" v-model="input_selection" @change="inputSelectionChanged">
                <label :for="radio1_uid">Enter Below</label>
              </div>
              <input v-if="input_selection == 'value'" class="input" type="text" @blur="change($event)" @dblclick.stop="" v-model="value" />
              <div class="radio">
                <input type="radio" :id="radio2_uid" value="input" v-model="input_selection" @change="inputSelectionChanged">
                <label :for="radio2_uid">Select From "URL" Input</label>
              </div>
            </div>`,
            data() {
                return {
                    type: drupalSettings.if_then_else.nodes.page_redirect_action.type,
                    class: drupalSettings.if_then_else.nodes.page_redirect_action.class,
                    name: drupalSettings.if_then_else.nodes.page_redirect_action.name,
                    input_selection: 'value',
                    value: ''
                }
            },
            methods: {
                change(e) {
                    this.value = e.target.value;
                    this.update();
                },
                update() {
                    if (this.ikey) {
                        this.putData('value', this.value);
                    }
                    editor.trigger('process');
                },
                inputSelectionChanged() {
                    this.putData('input_selection', this.input_selection);
                    editor.trigger('process');
                }
            },
            mounted() {
                this.putData('type',drupalSettings.if_then_else.nodes.page_redirect_action.type);
                this.putData('class',drupalSettings.if_then_else.nodes.page_redirect_action.class);
                this.putData('name', drupalSettings.if_then_else.nodes.page_redirect_action.name);

                this.input_selection = this.getData('input_selection');

                var get_value = this.getData('value');
                if (typeof get_value != 'undefined') {
                    this.value = get_value;
                }
                else {
                    this.value = '';
                }
            },
            created() {
                //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
                this.radio1_uid = _.uniqueId('radio_');
                this.radio2_uid = _.uniqueId('radio_');
            }
        };
        this.props = { emitter, ikey: key, readonly };
    }

    setValue(value) {
        this.vueContext.value = value;
    }
}