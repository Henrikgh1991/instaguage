<template>
    <div class="question scale">
        <modal ref="rangeDlg" class="range-dlg">
            <span slot="title">Templates</span>
            <span slot="content">
                <ul class="nav nav-tabs" style="list-style: none;">
                    <li class="nav-item" :class="{ active : (rangeDlg.tab == 'range') }">
                        <a class="nav-link"
                            @click.stop="rangeDlg.tab = 'range'">
                            Range
                        </a>
                    </li>
                    <li class="nav-item" :class="{ active : (rangeDlg.tab == 'smilies') }">
                        <a class="nav-link"
                            @click.stop="rangeDlg.tab = 'smilies'">
                            Smilies
                        </a>
                    </li>
                    <li class="nav-item" :class="{ active : (rangeDlg.tab == 'thumbs') }">
                        <a class="nav-link"
                            @click.stop="rangeDlg.tab = 'thumbs'">
                            Thumbs
                        </a>
                    </li>
                </ul>

                <div class="tab-pane" v-if="(rangeDlg.tab == 'range')">
                    <div class="form-group">
                        <label class="selected">Minimum value</label>
                        <input
                            class="form-control"
                            v-model="rangeDlg.min"
                            placeholder="1"
                            v-focus
                            step="1"
                            type="number">
                    </div>
                    <div class="form-group">
                        <label class="selected">Maximum value</label>
                        <input
                            class="form-control"
                            v-model="rangeDlg.max"
                            placeholder="5"
                            step="1"
                            type="number">
                    </div>
                </div> <!-- range -->

                <div class="tab-pane tab-smilies" v-if="(rangeDlg.tab == 'smilies')">
                    <p>
                        Add these smilies:
                    </p>

                    <span v-for="s in rangeDlg.smilies" :key="s">
                        {{ s }}
                    </span>                        
                </div> <!-- smilies -->

                <div class="tab-pane tab-thumbs" v-if="(rangeDlg.tab == 'thumbs')">
                    <p>
                        Add these thumbs:
                    </p>

                    <span v-for="s in rangeDlg.thumbs" :key="s">
                        {{ s }}
                    </span>                        
                </div> <!-- thumbs -->

            </span>
            <span slot="footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button"
                    :disabled="!rangeOK"
                    @click="onAddRange"
                    class="btn btn-primary" data-dismiss="modal">Add template!</button>
            </span>
        </modal>

        <div v-if="mode == 'query'" class="panel panel-default query-view"  :class="layoutClasses">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{ questionData.title }}</h3>
                </div>
            </div>

            <div class="panel-body">
                <div v-if="int_header" 
                    v-html="int_header"
                    class="q-scale-header q-scale-content"></div>

                <div class="option-list" v-if="!questionData.params || ((questionData.params.layout != 'dropdown') && (questionData.params.layout != 'slider'))">
                    <div class="radio" v-for="(i,iidx) in optionsList" :class="{ selected : (int_value == i) }" :key="iidx">
                        <label :class="optionLayoutClasses(i)">
                            <input type="radio" :value="i" v-model="int_value">
                            <span>{{ i.title }}</span>
                        </label>
                    </div>
                </div> <!-- option-list - non-dropdown & non-slider -->

                <div class="option-list" v-if="questionData.params && questionData.params.layout && questionData.params.layout == 'dropdown'">
                    <select class="form-control" v-model="int_value">
                        <option v-for="(i,iidx) in optionsList" :key="iidx" :value="i">
                            <span>{{ i.title }}</span>
                        </option>
                    </select>
                </div> <!-- option-list - dropdown -->

                <div class="option-list" v-if="questionData.params && questionData.params.layout && questionData.params.layout == 'slider'">
                    <vue-slider 
                        class="slider"
                        ref="slider"
                        @callback="int_value = $event"
                        :options="sliderOptions"
                        :data="sliderData"
                        :piecewise="true"
                        :formatter="sliderTooltipFormatter"
                        tooltip="always" />
                </div> <!-- option-list - slider -->

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
                    placeholder="single choice title" 
                    v-focus
                    type="text">
            </div>

            <div class="row form-group" title="Option layout">
                <div class="col-md-3 col-sm-3">
                    <div class="radio" title="left-to-right">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="horizontal">
                            <font-awesome-icon icon="list" size="lg" :transform="{ rotate: 90 }"/>
                            options in columns
                        </label>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <div class="radio" title="top-to-bottom">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="vertical">
                            <font-awesome-icon icon="list" size="lg" />
                            options in rows
                        </label>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <div class="radio" title="dropdown">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="dropdown">
                            <font-awesome-icon icon="caret-square-down" size="lg" />
                            options as dropdown
                        </label>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <div class="radio" title="slider">
                        <label>
                            <input type="radio" v-model="questionData.params.layout" value="slider">
                            <font-awesome-icon icon="sliders-h" size="lg" />
                            options as slider
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
                    <button type="button"
                        title="Templates ..."
                        @click.stop="queryAddRange(questionData)"
                        class="btn btn-default">
                        <font-awesome-icon icon="forward"/>
                        templates &hellip;
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
        </div>

    </div>
</template>

<style lang="scss" scoped>
.question.scale {
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

/*
            select {
                background-color:#aaffaa;
            }
*/

            > div {
                flex-grow:1;
                border:dotted 1px #ddd;
                border-radius:0.25em;
                text-align:center;
                margin-top:5px;
                margin-left:0.25em;
                margin-right:0.25em;

                &.slider {
                    margin-top:1em;
                }

                &.selected {
                    background-color:#aaffaa;
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

            select {
                background-color:#fee;
            }

            border-radius:0.25em;
            background-color:#fee;
        }    
    }
}

.range-dlg {
    .nav-tabs {

        a {
            cursor:pointer;
        }
    }

    .tab-smilies {
        span {
            font-size:x-large;
            width:20%;
            text-align:center;
            display:inline-block;
        }
    }

    .tab-thumbs {
        span {
            font-size:x-large;
            width:33%;
            text-align:center;
            display:inline-block;
        }
    }
}
</style>

<script>

import VueSlider from 'vue-slider-component'
import Modal from '../Modal.vue'

export default {
    name : "Scale",
    questionMetadata : {
        type : "scale",
        title : "single choice"
    },
    data : function() {
        return {
            sliderData : null,
            sliderOptions : {
            },
            rangeDlg : {
                smilies : [ "😁", "😃", "😐", "😒", "😠" ],
                thumbs : [ "👍", "✋",  "👎" ],
                tab : 'range',
                min : 1,
                max : 5,
                questionData : null
            },
            optionsList : [],
            int_value : null,
            int_header : null,
            int_footer : null
        }
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

        window.addEventListener("load", me.refreshSliders);
    },
    beforeDestroy : function() {
        window.removeEventListener("load", this.refreshSliders);
    },
    computed : {
        rangeOK() {
            var retval = true;

            try {
                retval = retval && (this.rangeDlg.min && (this.rangeDlg.min == Math.floor(this.rangeDlg.min)));
                retval = retval && (this.rangeDlg.min && (this.rangeDlg.max == Math.floor(this.rangeDlg.max)));
                retval = retval && (this.rangeDlg.max > this.rangeDlg.min);
            } catch(e) {
                retval = false;
            }

            return retval;
        },
        layoutClasses() {
            var retval = [];

            if(!this.allOK) retval.push("invalid");

            if(this.questionData.params && this.questionData.params.layout) {
                retval.push("layout-" + this.questionData.params.layout);
            }

            return retval.join(" ");
        },
        allOK : function() {
            return this.optional || (this.int_value !== null);
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        refreshSliders() {
            var me = this;

            if(me.$refs.slider) {
                me.$refs.slider.refresh();
            }
        },
        optionLayoutClasses(o) {
            var retval = "";

            if(o.params && o.params.layout) {
                retval = o.params.layout;
            }

            return retval;
        },
        onAddRange() {
            switch(this.rangeDlg.tab) {
            case "range":
                for(var i = this.rangeDlg.min; i <= this.rangeDlg.max; i++) {
                    this.rangeDlg.questionData.options.push({
                        id : (++this.runningId),
                        title : i
                    });
                }
                break;

            case "smilies":
            case "thumbs":
                for(var i = 0; i < this.rangeDlg[this.rangeDlg.tab].length; i++) {
                    this.rangeDlg.questionData.options.push({
                        id : (++this.runningId),
                        title : this.rangeDlg[this.rangeDlg.tab][i],
                        params : {
                            layout : "large-font"
                        }
                    });
                }
                break;
            }
        },
        queryAddRange(questionData) {
            var me = this;
            me.rangeDlg.questionData = questionData;
            me.$refs.rangeDlg.show();
        },
        sliderTooltipFormatter(v) {
            if(!v) return "";
            return v.title;
        },
        checkValidity : function(questionData) {
            return (questionData.title.trim().length > 0) && (questionData.options.length > 0);
        },
        pc : function(q,o) {
            var curVal = q.votes[o.id];
            if(curVal === undefined) curVal = 0;
            return Math.round(100.0 *  curVal / q.votes.total);
        },
        newQuestionData : function() {
            return {
                type : "scale",
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
            this.int_value = null;
            this.sliderData = newV;
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
        
        VueSlider,
        Modal
    }
}
</script>