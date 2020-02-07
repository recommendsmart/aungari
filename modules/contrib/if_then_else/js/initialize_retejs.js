// Defining sockets
let sockets = {}
let inverseSocketsMap = {}
for (let socketName in drupalSettings.if_then_else.sockets) {
    sockets[socketName] = new Rete.Socket(drupalSettings.if_then_else.sockets[socketName]);
    inverseSocketsMap[drupalSettings.if_then_else.sockets[socketName]] = socketName;
}

// This js handles the front end for if then else ui
var container = document.querySelector('#rete-editor');
var editor = new Rete.NodeEditor('demo@0.1.0', container);

var clickPositionX = 0, clickPositionY = 0;
v = new Vue({
    el: '#dock',
    data: {
        searchKey: '',
        items: []
    },
    computed: {
        filtered() {
            this.items.sort( function( a, b ){
              return ( ( a.type == b.type ) ? 0 : ( ( a.type > b.type ) ? 1 : -1 ) );
            });

            if (!this.searchKey) return this.items;

            return this.items.filter(item => {
                return item.title.toLowerCase().includes(this.searchKey.toLowerCase());
            });
        }
    },
    methods: {
        dragstart: function (item, e) {
            if(!e.dataTransfer) return;
            e.dataTransfer.setData('componentName', item.name);
        },
        async drop(item, e) {
            const component = editor.components.get(item.title);

            if(!component) {
                console.err('Component ${item.title} not found');
                return;
            }

            // force update the mouse position
            const { screenX, screenY } = e;
            const rect = editor.view.area.el.getBoundingClientRect();
            const x = screenX - rect.left;
            const y = screenY - rect.top - 75;
            const k = editor.view.area.transform.k;

            editor.view.area.mouse = { x: x / k, y: y / k };
            editor.view.area.trigger('mousemove', { ...editor.view.area.mouse }); // TODO rename on `pointermove`

            //const node = createNode(component, editor.view.area.mouse);
            const node = await component.createNode({});
            node.position[0] = editor.view.area.mouse.x;
            node.position[1] = editor.view.area.mouse.y;

            editor.addNode(node);
        },
        async click(item, e) {
            if (clickPositionX || clickPositionY) {
                const component = editor.components.get(item.title);
                const node = await component.createNode({});
                node.position[0] = clickPositionX;
                node.position[1] = clickPositionY;

                editor.addNode(node);
            }
        }
    },
    template: '<div class="dock"><div id="search-container"><div class="search-wrapper"><input placeholder="Search ..." v-model="searchKey" /></div></div><ul><li v-tooltip.top="{content: item.description, delay: {show: 1000}}" v-bind:class="(item.type)" v-for="item in filtered" v-bind:key="item.title" draggable="true" v-on:dragstart="dragstart(item, $event)" v-on:dragover="$event.preventDefault()" v-on:dragend="drop(item, $event)" v-on:click="click(item, $event)">{{item.title}}</li></ul></div>'
});

editor.on('componentregister', async c => {
     let type = c.name.split(':');
     v.$data.items.push({title: c.name, type: type[0], description: c.description});
});

editor.on('click', () => {
    clickPositionX = editor.view.area.mouse.x;
    clickPositionY = editor.view.area.mouse.y;
});

editor.on('zoom', ({source}) => {
    return source !== 'dblclick';
});

editor.use(ConnectionPlugin.default);
editor.use(VueRenderPlugin.default);
editor.use(NodeContextMenuPlugin.default);
editor.use(AreaPlugin);
editor.use(CommentPlugin.default);
editor.use(HistoryPlugin.default);

var engine = new Rete.Engine('demo@0.1.0');
