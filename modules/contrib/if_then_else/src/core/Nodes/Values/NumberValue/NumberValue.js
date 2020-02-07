class NumberValueControl extends Rete.Control {

    constructor(emitter, key, readonly) {
        super(key);
        this.component = {
            props: ['ikey', 'getData', 'putData', 'emitter'],
            template: '<input type="number" step="any" :value="value" @input="change($event)" @dblclick.stop=""/>',
            data() {
                return {
                    type: drupalSettings.if_then_else.nodes.number_value.type,
                    class: drupalSettings.if_then_else.nodes.number_value.class,
                    name: drupalSettings.if_then_else.nodes.number_value.name,
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
                }
            },
            mounted() {
                this.putData('type',drupalSettings.if_then_else.nodes.number_value.type);
                this.putData('class',drupalSettings.if_then_else.nodes.number_value.class);
                this.putData('name', drupalSettings.if_then_else.nodes.number_value.name);

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
