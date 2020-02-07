class TextValueControl extends Rete.Control {

    constructor(emitter, key, readonly) {
        super(key);
        this.component = {
            props: ['ikey', 'getData', 'putData', 'emitter'],
            template: `<div class="fields-container">
              <textarea class="input" type="text" @blur="change($event)" @dblclick.stop=""  v-model="value"></textarea>
              
              <div class="expend_textarea_button" @click="popupActive=true" >Expend</divclass>

              <vs-popup class="holamundo"  title="Text" :active.sync="popupActive">
                <textarea class="input" type="text"  v-model="value" rows="10" cols="70" @blur="change($event)" @dblclick.stop=""></textarea>
                <vs-button @click="popupActive=false" color="primary" type="filled">Save</vs-button>
              </vs-popup>
            </div>`,
            data() {
                return {
                    type: drupalSettings.if_then_else.nodes.text_value.type,
                    class: drupalSettings.if_then_else.nodes.text_value.class,
                    name: drupalSettings.if_then_else.nodes.text_value.name,
                    value: '',
                  popupActive:false,
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
                }
            },
            mounted() {
                this.putData('type',drupalSettings.if_then_else.nodes.text_value.type);
                this.putData('class',drupalSettings.if_then_else.nodes.text_value.class);
                this.putData('name', drupalSettings.if_then_else.nodes.text_value.name);

                var get_value = this.getData('value');
                if (typeof get_value != 'undefined') {
                    this.value = get_value;
                }
                else {
                    this.value = '';
                }
            }
        };
        this.props = { emitter, ikey: key, readonly };
    }

    setValue(value) {
        this.vueContext.value = value;
    }
}
