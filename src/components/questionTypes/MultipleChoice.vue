<template>
    <div class="question multiple-choice">
        <div v-if="mode == 'query'" class="panel panel-default query-view" :class="layoutClasses">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{ questionData.title }}</h3>
                </div>
            </div>

            <div class="panel-body">
                <div v-if="int_header" 
                    v-html="int_header"
                    class="q-scale-header q-scale-content"></div>

                <div class="option-list">
                    <div class="radio" v-for="(i,iidx) in optionsList" :class="{ selected : (int_value.indexOf(i) > -1) }" :key="iidx">
                        <label>
                            <input type="checkbox" :value="i" @change="onSelectionChanged($event, i)">
                            <span>{{ i.title }}</span>
                        </label>
                    </div>
                </div> <!-- option-list -->

                <div v-if="int_footer" 
                    v-html="int_footer"
                    class="q-scale-footer q-scale-content">
                </div>
            </div>
        </div> <!-- query -->

        <div v-if="mode == 'edit'">
            <div class="form-group" :class="{ invalid : (questionData.title.length == 0) }">
                <label class="selected">Title</label>
                <input 
                    class="form-control"
                    v-model="questionData.title" 
                    v-focus
                    placeholder="multiple choice title" 
                    type="text">
            </div>

            <div class="row form-group" title="Option layout">
                <div class="col-md-6 col-sm-6">
                    <div class="radio" title="left-to-right">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="horizontal">
                            <font-awesome-icon icon="list" size="lg" :transform="{ rotate: 90 }"/>
                            options in columns
                        </label>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="radio" title="top-to-bottom">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="vertical">
                            <font-awesome-icon icon="list" size="lg" />
                            options in rows
                        </label>
                    </div>
                </div>
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
                        add answer option!
                    </button>
                </li>
            </ol>                            
        </div> <!-- edit -->

        <div v-if="mode == 'result'">
            <h3>{{ questionData.title }}</h3>
            <div class="options-list">
                <div class="option-container" v-for="o in questionData.options" :title="questionData.votes[o.id]" :key="o.id">
                    <div class="option-value animated slideInLeft"
                        v-if="questionData.votes.total > 0"   
                        :style="{ width : pc(questionData,o) + '%' }">
                        <span class="option-title">{{ o.title }}</span>
                        <span class="option-percentage pull-right">{{ pc(questionData,o) }}%</span>
                    </div>
                </div>
            </div>
            <p class="pull-right">Total: {{ questionData.votes.total }}</p>            
        </div> <!-- result -->

    </div>
</template>

<style lang="scss" scoped>
.question.multiple-choice {
    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }

    .query-view {
        .panel-title {
            width:auto;
        }

        .option-list {
            display:flex;
            flex-direction:row;
            border:solid 1px transparent;

            > div {
                flex-grow:1;
                border:dotted 1px #ddd;
                border-radius:0.25em;
                text-align:center;
                margin-top:5px;
                margin-left:0.25em;
                margin-right:0.25em;

                &.selected {
                    background-color:#aaffaa;
                }

                label {
                    display:block;
                    padding:0.5em;

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
    .query-view.layout-vertical {
        .option-list {
            flex-direction:column;

            > div {
                text-align:left;

                label span {
                    display:inline;
                }
            }
        }
    }

    .query-view.invalid {
        .option-list {
            border:solid 1px #900;
            border-radius:0.25em;
            background-color:#fee;
        }    
    }

    .radio {
        > label {
            display:block;
        }
    }
}
</style>

<script>


export default {
    name : "MultipleChoice",
    questionMetadata : {
        type : "multipleChoice",
        title : "multiple choice"
    },    
    data : function() {
        return {
            optionsList : [],
            int_value : [],
            int_header : null,
            int_footer : null
        }
    },
    created : function() {
        if(this.footer && this.footer.length) {
            this.int_footer = this.footer;
        }

        if(this.header && this.header.length) {
            this.int_header = this.header;
        }

        this.rebuildOptionsList();
        this.onResultChanged(this.getResult());
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
            return this.optional || (this.int_value.length > 0);
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        checkValidity : function(questionData) {
            return (questionData.title.trim().length > 0) && (questionData.options.length > 0);
        },
        pc : function(q,o) {
            var curVal = q.votes[o.id];
            if(curVal === undefined) curVal = 0;
            return Math.round(100.0 *  curVal / q.votes.total);
        },
        onSelectionChanged : function(ev, i) {
            if(ev.target.checked) {
                this.int_value.push(i);
            } else {
                this.int_value.splice(this.int_value.indexOf(i), 1);
            }
        },
        newQuestionData : function() {
            return {
                type : "multipleChoice",
                title : "",
                id : (++this.runningId),
                options : [ ],
                params : {
                    layout : "horizontal"
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
                value : this.int_value
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
            this.int_value = [];
            this.onResultChanged(this.getResult());            
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
        
    }
}
</script>