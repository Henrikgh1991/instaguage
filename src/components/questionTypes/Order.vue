<template>
    <div class="question order">

        <div v-if="mode == 'query'" class="panel panel-default query-view"  :class="layoutClasses">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{ questionData.title }}</h3>
                </div>
            </div>

            <div class="panel-body">
                <div v-if="int_header" 
                    v-html="int_header"
                    class="q-order-header q-order-content"></div>

                <div class="option-list" @click.stop="didMove = true" v-if="optionsList">
                    <draggable v-model="optionsList" @start="didMove = true">
                        <div v-for="o in optionsList" :key="o.id" class="item">
                            <button class="btn btn-default" title="move item">
                                <font-awesome-icon icon="arrows-alt" />
                            </button>

                            {{ o.title }}
                        </div>
                    </draggable>
                </div> <!-- option-list -->

                <div v-if="int_footer" 
                    v-html="int_footer"
                    class="q-order-footer q-order-content">
                </div>
            </div>
        </div> <!-- query -->

        <div v-if="mode == 'edit'">
            <div class="form-group" :class="{ invalid : (questionData.title.length == 0) }">
                <label class="selected">Title</label>
                <input 
                    class="form-control"
                    v-model="questionData.title" 
                    placeholder="item order title" 
                    v-focus
                    type="text">
            </div>

            <ol class="option-list">
                <li v-for="(o, oidx) in questionData.options" :key="oidx">
                    <div class="row">
                        <div class="col-md-11 col-sm-10 col-xs-10">
                            <div class="form-group">
                                <label class="selected">Label</label>
                                <input
                                    class="form-control" 
                                    type="text" 
                                    v-focus
                                    v-model="o.title">
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-2 col-xs-2">
                            <button type="button"
                                title="Remove this option!"
                                @click.stop="questionData.options.splice(oidx, 1)"
                                class="btn btn-default btn-block delete-option-button">
                                <font-awesome-icon icon="trash"/>
                            </button>
                        </div>
                    </div>
                </li>
                <li>
                    <button type="button"
                        title="Add new option!"
                        @click.stop="addOption(questionData)"
                        class="btn btn-default">
                        <font-awesome-icon icon="plus"/>
                        add draggable item!
                    </button>
                </li>
            </ol>                            
        </div> <!-- edit -->

        <div v-if="mode == 'result'" class="result-view">
            <h3>{{ questionData.title }}</h3>
            <ol class="option-list">
                <li class="item" v-for="(o,oidx) in results(questionData)" :key="o.id" :title="questionData.votes.rawData[oidx].value">
                    {{ o.title }}
                </li>
            </ol>
            <p class="pull-right">Total: {{ questionData.votes.total }}</p>
        </div>

    </div>
</template>

<style lang="scss" scoped>
.question.order {
    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }

    .result-view {
        .item {
            padding:0.25em;
        }

        .option-list {
            list-style-type:decimal;
        }
    }

    .query-view {
        .option-list {
            border:solid 1px transparent;

            .item {
                border:dotted 1px #ddd;
                border-radius:0.25em;
                text-align:left;
                margin-top:5px;
                margin-left:0.25em;
                margin-right:0.25em;
                cursor:move;
                padding:0.5rem;
                background-color:#eee;

                button {
                    margin-right:1em;
                }

                label {
                    display:block;
                    padding:0.5em;

                    &.large-font {
                        font-size:x-large;
                    }

                    input, span {
                        margin:0;
                        position:relative;
                    }

                    span {
                        display:block;
                    }
                }
            }
        }
    }
    
    .query-view.invalid {
        .option-list {
            border:solid 1px #900;

            select {
                background-color:#fee;
            }

            border-radius:0.25em;
            background-color:#fee;
        }    
    }
}
</style>

<script>

import Modal from '../Modal.vue'
import draggable from 'vuedraggable'

export default {
    name : "Order",
    questionMetadata : {
        type : "order",
        title : "item order"
    },
    data : function() {
        return {
            didMove : false,
            optionsList : null,
            int_value : null,
            int_header : null,
            int_footer : null
        };
    },
    created : function() {
        var me = this;

        if(this.footer && this.footer.length) {
            this.int_footer = this.footer;
        }

        if(this.header && this.header.length) {
            this.int_header = this.header;
        }

        this.rebuildOptionsList();
        this.onResultChanged(this.getResult());
    },
    beforeDestroy : function() {
    },
    computed : {
        layoutClasses() {
            var retval = [];

            if(!this.allOK) retval.push("invalid");

            if(this.questionData.params && this.questionData.params.layout) {
                retval.push("layout-" + this.questionData.params.layout);
            }

            return retval.join(" ");
        },
        allOK : function() {
            return this.optional || this.didMove;
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        results(questionData) {
            var oMap = {};
            questionData.options.forEach((e) => { oMap[e.id] = e; });
            if(!questionData.votes[questionData.id]) return [];
            return questionData.votes[questionData.id].map(e => oMap[e]).filter(e => (e !== undefined));
        },
        optionLayoutClasses(o) {
            var retval = "";

            if(o.params && o.params.layout) {
                retval = o.params.layout;
            }

            return retval;
        },
        checkValidity : function(questionData) {
            var retval = (questionData.title.trim().length > 0) && (questionData.options.length > 0);

            for(var idx in questionData.options) {
                var o = questionData.options[idx];
                if(o.title.length == 0) {
                    retval = false;
                }
            }

            return retval;
        },
        pc : function(q,o) {
            var curVal = q.votes[o.id];
            if(curVal === undefined) curVal = 0;
            return Math.round(100.0 *  curVal / q.votes.total);
        },
        newQuestionData : function() {
            return {
                type : "order",
                title : "",
                id : (++this.runningId),
                options : [ ],
                params : {
                }
            };
        },
        addOption : function(q) {
            q.options.push({
                id : (++this.runningId),
                title : ""
            });
        },
        getResult : function() {            
            return {
                ok : this.allOK,
                value : this.optionsList
            }
        },
        rebuildOptionsList : function() {
            var newList = [];
            newList = this.questionData.options;
            this.$set(this, "optionsList", newList);
        }
    },
    watch : {
        optionsList : function(newV, oldV) {
            this.int_value = null;
            this.onResultChanged(this.getResult());            
        },
        didMove : function(newV) {
            if(newV) {
                this.onResultChanged(this.getResult());            
            }
        },
        header : function(newV, oldV) {
            this.int_header = newV;
        },
        footer : function(newV, oldV) {
            this.int_footer = newV;
        },
        int_value : {
            deep : true,
            handler : function(newV, oldV) {
                this.onResultChanged(this.getResult());
            }
        },
    },
    props : {
        footer : {
            default : ""
        },
        header : {
            default : ""
        },
        optional : {
            default : false
        },
        onResultChanged : {
            type : Function,
            default : function(v) { }
        },
        mode : {
            default : "query"
        },
        questionData : {
            type : Object,
            default : function() {
                return {
                    title : "",
                    options : []
                };
            }
        }
    },
    components : {
        
        Modal,
        draggable
    }
}
</script>