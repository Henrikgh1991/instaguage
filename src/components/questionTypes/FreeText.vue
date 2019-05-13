<template>
    <div class="question free-text">
        <div v-if="mode == 'query'" class="panel panel-default query-view" :class="{ invalid : !allOK }">
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
                    <textarea v-model="questionData.content" placeholder="data protection hint: please do not provide any personal data within your answer"></textarea>
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
                    placeholder="free text title" 
                    type="text">
            </div>
        </div> <!-- edit -->

        <div v-if="mode == 'result'">
            <h3>{{ questionData.title }}</h3>
            <div class="options-list">
                <ol>
                    <li v-for="(v, vidx) in questionData.votes[questionData.id]" :key="vidx">{{ v }}</li>
                </ol>
            </div>
            <p class="pull-right">Total: {{ questionData.votes.total }}</p>            
        </div> <!-- result -->

    </div>
</template>

<style lang="scss" scoped>
.question.free-text {
    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }

    .query-view {
        .option-list {
            display:flex;
            flex-direction:row;
            border:solid 1px transparent;

            > textarea {
                width:100%;
                min-height:8em;

                &:-ms-input-placeholder {
                    color:#ccc;
                }

                &::placeholder  {
                    color:#ccc;
                }
            }
        }
    }

    .options-list {
        border-left:none;

        > ol {
            list-style:none;
            margin-left:0;
            padding-left:0;
            max-height:8em;
            overflow:auto;

            > li {
                padding-bottom:0.5em;
                padding-top:0.5em;
                white-space: pre-wrap;

                & + li {
                    border-top: solid 1px #999;
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
}
</style>

<script>


export default {
    name : "FreeText",
    questionMetadata : {
        type : "freeText",
        title : "free text"
    },    
    data : function() {
        return {
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

        this.onResultChanged(this.getResult());
    },
    computed : {
        allOK : function() {
            return this.optional || (this.questionData.content.length > 0);
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        checkValidity : function(questionData) {
            return (questionData.title.trim().length > 0);
        },
        newQuestionData : function() {
            return {
                type : "freeText",
                title : "",
                id : (++this.runningId),
                content : ""
            };
        },
        getResult : function() {            
            return {
                ok : this.allOK,
                value : this.questionData
            }
        },
    },
    watch : {
        header : function(newV, oldV) {
            this.int_header = newV;
        },
        footer : function(newV, oldV) {
            this.int_footer = newV;
        },
        questionData : {
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
            default : true
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
                    content : ""
                };
            }
        }
    },
    components : {
        
    }
}
</script>