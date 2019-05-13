<template>
    <div class="question matrix">

        <div v-if="mode == 'query'" class="panel panel-default query-view"  :class="layoutClasses">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{ questionData.title }}</h3>
                </div>
            </div>

            <div class="panel-body">
                <div v-if="int_header" 
                    v-html="int_header"
                    class="q-matrix-header q-matrix-content"></div>

                <div class="option-list cloud" v-if="!questionData.params.mode || (questionData.params.mode == 'cloud')">
                    <div class="axis" 
                        :class="{ invalid : (int_value.x == null) }">
                        <h4>{{ questionData.options[0].title }}</h4>

                        <vue-slider 
                            class="slider"
                            ref="slider-X"
                            v-if="rangeValid(questionData.options[0])"
                            @drag-start="int_value.x = $event.getValue()"
                            @callback="int_value.x = $event"
                            :min="questionData.options[0].min"
                            :max="questionData.options[0].max"
                            :value="questionData.options[0].min"
                            tooltip="always" />
                    </div>

                    <div class="axis" 
                        :class="{ invalid : (int_value.y == null) }">
                        <h4>{{ questionData.options[1].title }}</h4>

                        <vue-slider 
                            class="slider"
                            ref="slider-Y"
                            v-if="rangeValid(questionData.options[1])"
                            @drag-start="int_value.y = $event.getValue()"
                            @callback="int_value.y = $event"
                            :min="questionData.options[1].min"
                            :max="questionData.options[1].max"
                            :value="questionData.options[1].min"
                            tooltip="always" />
                    </div>
                </div> <!-- cloud -->

                <div class="option-list items" v-if="(questionData.params.mode == 'items')">
                    <div v-for="(i,iidx) in questionData.items" :key="iidx">
                        <h4>{{ i.title }}</h4>

                        <div class="axis" 
                            :class="{ invalid : (int_value[i.id].x == null) }">
                            <h5>{{ questionData.options[0].title }}</h5>

                            <vue-slider 
                                class="slider"
                                :ref="'slider-X-' + iidx"
                                v-if="rangeValid(questionData.options[0])"
                                @drag-start="int_value[i.id].x = $event.getValue()"
                                @callback="int_value[i.id].x = $event"
                                :min="questionData.options[0].min"
                                :max="questionData.options[0].max"
                                :value="questionData.options[0].min"
                                tooltip="always" />
                        </div>

                        <div class="axis" 
                            :class="{ invalid : (int_value[i.id].y == null) }">
                            <h5>{{ questionData.options[1].title }}</h5>

                            <vue-slider 
                                class="slider"
                                :ref="'slider-Y-' + iidx"
                                v-if="rangeValid(questionData.options[1])"
                                @drag-start="int_value[i.id].y = $event.getValue()"
                                @callback="int_value[i.id].y = $event"
                                :min="questionData.options[1].min"
                                :max="questionData.options[1].max"
                                :value="questionData.options[1].min"
                                tooltip="always" />
                        </div>
                    </div>
                </div> <!-- items -->

                <div v-if="int_footer" 
                    v-html="int_footer"
                    class="q-matrix-footer q-matrix-content">
                </div>
            </div>
        </div> <!-- query -->

        <div v-if="mode == 'edit'">
            <div class="form-group" :class="{ invalid : (questionData.title.length == 0) }">
                <label class="selected">Title</label>
                <input 
                    class="form-control"                
                    v-model="questionData.title" 
                    placeholder="matrix title" 
                    v-focus
                    type="text">
            </div>

            <div class="row form-group" title="Option layout">
                <div class="col-md-6 col-sm-6">
                    <div class="radio" title="distribution for one item">
                        <label>
                            <input type="radio" v-model="questionData.params.mode" value="cloud">
                            <font-awesome-icon icon="braille" size="lg" />
                            distribution for one item
                        </label>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="radio" title="mean value for item(s)">
                        <label>
                            <input type="radio" v-model="questionData.params.mode" value="items">
                            <font-awesome-icon icon="list" size="lg" />
                            mean value for item(s)
                        </label>
                    </div>
                </div>
            </div>      

            <h4>Dimensions</h4>

            <ol class="option-list">
                <li v-for="(o, oidx) in questionData.options" :key="oidx">
                    <div class="row">
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <div class="form-group" :class="{ invalid : (o.title.length == 0) }">
                                <label class="selected">{{ o.label }}</label>
                                <input type="text" class="form-control form-control-plaintext"
                                    v-model="o.title">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-3 col-xs-6" :class="{ invalid : !rangeValid(o) }">
                            <div class="form-group">
                                <label class="selected">Min</label>
                                <input type="number" step="1" class="form-control form-control-plaintext"
                                    v-model.number="o.min">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-3 col-xs-6" :class="{ invalid : !rangeValid(o) }">
                            <div class="form-group">
                                <label class="selected">Max</label>
                                <input type="number" step="1" class="form-control form-control-plaintext"
                                    v-model.number="o.max">
                            </div>
                        </div>

                    </div>
                </li>
            </ol>

            <div v-if="questionData.params.mode == 'items'">
                <h4>Items</h4>

                <ol class="item-list">
                    <li v-for="(o, oidx) in questionData.items" :key="oidx">
                        <div class="row">
                            <div class="col-md-11 col-sm-10 col-xs-10">
                                <div class="form-group" :class="{ invalid : (o.title.length == 0) }">
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
                                    @click.stop="questionData.items.splice(oidx, 1)"
                                    class="btn btn-default btn-block delete-option-button">
                                    <font-awesome-icon icon="trash"/>
                                </button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <button type="button"
                            title="Add new item!"
                            @click.stop="addItem(questionData)"
                            class="btn btn-default">
                            <font-awesome-icon icon="plus"/>
                            add item!
                        </button>
                    </li>
                </ol>
            </div>

        </div> <!-- edit -->

        <div v-if="mode == 'result'">
            <h3>{{ questionData.title }}</h3>

            <div class="row">
                <div class="col-md-6">
                    <scatter-chart ref="scatterChart" :chartData="chartData" :options="chartOptions" />
                </div>
            </div>

            <p class="pull-right">Total: {{ questionData.votes.total }}</p>
        </div>

    </div>
</template>

<style lang="scss" scoped>
.question.matrix {
    overflow:hidden;

    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }

    .axis.invalid {
        border:solid 1px #900;
        background-color:#fee;
    }

    .query-view {
        .option-list {
            border:solid 1px transparent;
            padding:0.5em;

            input + input {
                margin-top:0.5em;
            }
        }
    }

    .option-list.cloud {
        h4 {
            margin-bottom:3.5rem;
        }
    }

    .option-list.items {
        h5 {
            margin-bottom:3.5rem;
        }

        .axis {
            margin-left:2rem;
        }
    }

    .query-view.invalid {
        .option-list {
            border:solid 1px #900;
            border-radius:0.25em;
            background-color:#fee;

            input {
                background-color:#fee;
            }
        }
    }
}
</style>

<script>

import Modal from '../Modal.vue'
import VueSlider from 'vue-slider-component'
import ScatterChart from '../ScatterChart.vue'
import Chance from 'chance'

export default {
    name : "Matrix",
    questionMetadata : {
        type : "matrix",
        title : "point cloud (matrix)"
    },
    data : function() {
        return {
            itemColors : [
                '#00646e',
                '#af235f',
                '#ffb900',
                '#41aaaa',
                '#005f87',
                '#aab414',
                '#50bed7',
                '#007373',
                '#641946',
                '#647d2d',
            ],
            chartOptions : {
                layout : {
                    padding : 20
                },
				responsive: true,
                tooltips : {
                    enabled : true,
                    callbacks : {
                        label : this.tooltipLabel
                    }
                },
                legend : {
                    display : false
                },                
				scales: {
					xAxes: [
                        {
                            type : "linear",
                            scaleLabel : {
                                display : true,
                                labelString : "",
                            },
                            ticks : {
                                beginAtZero : true,
                                min : -5,
                                max : 5,
                                stepSize : 1
                            },
                            gridLines : {
                            }
                        },
                    ],
                    yAxes : [
                        {
                            type : "linear",
                            scaleLabel : {
                                display : true,
                                labelString : "",
                            },                            
                            ticks : {
                                beginAtZero : true,
                                min : -5,
                                max : 5,
                                stepSize : 1
                            }
                        },
                    ],
				}
            },
            chartData : {  
                datasets: [
                    {
                        label : "",
                        pointRadius : [ ],
                        pointHoverRadius : [ ],
                        backgroundColor: '#006487',
                        data: [
                        ]
                    }
                ]
            },
            int_value : null,
            int_header : null,
            int_footer : null
        }
    },
    updated : function() {
        this.refreshSliders();
    },
    created : function() {
        var me = this;

        if(this.footer && this.footer.length) {
            this.int_footer = this.footer;
        }

        if(this.header && this.header.length) {
            this.int_header = this.header;
        }

        this.updateIntValue();
        this.onResultChanged(this.getResult());

        this.chartOptions.scales.xAxes[0].ticks.min = this.questionData.options[0].min;
        this.chartOptions.scales.xAxes[0].ticks.max = this.questionData.options[0].max;
        this.chartOptions.scales.yAxes[0].ticks.min = this.questionData.options[1].min;
        this.chartOptions.scales.yAxes[0].ticks.max = this.questionData.options[1].max;
        this.chartOptions.scales.yAxes[0].scaleLabel.labelString = this.questionData.options[1].title;
        this.chartOptions.scales.xAxes[0].scaleLabel.labelString = this.questionData.options[0].title;

        if(this.questionData.params.mode && (this.questionData.params.mode == 'items')) {
            this.chartOptions.legend.display = true;
            this.chartOptions.tooltips.callbacks.label = this.tooltipLabel_item;
        }

        this.updatePoints();

        window.addEventListener("load", me.refreshSliders);
    },
    computed : {
        layoutClasses() {
            var retval = [];

            //if(!this.allOK) retval.push("invalid");

            if(this.questionData.params && this.questionData.params.layout) {
                retval.push("layout-" + this.questionData.params.layout);
            }

            return retval.join(" ");
        },
        allOK : function() {
            if(this.optional) return true;

            if(this.questionData.params.mode && (this.questionData.params.mode == 'items')) {
                var retval = true;

                for(var idx in this.int_value) {
                    var o = this.int_value[idx];
                    retval = retval && (o.x !== null);
                    retval = retval && (o.y !== null);
                }

                return retval;
            } else {
                if(this.int_value.x == null) return false;
                if(this.int_value.y == null) return false;
            }

            return true;
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    beforeDestroy : function() {
        window.removeEventListener("load", this.refreshSliders);
    },
    methods : {
        updateIntValue() {
            switch(this.questionData.params.mode) {
            case "items":
                this.int_value = { };
                for(var idx in this.questionData.items) {
                    this.$set(this.int_value, this.questionData.items[idx].id, { x : null, y : null });
                }
                break;

            default:
                this.int_value = { x : null, y : null };
                break;
            }
        },
        addItem(q) {
            q.items.push({
                id : (++this.runningId),
                title : ""
            });
        },
        tooltipLabel_item(item, data) {
            var d = data.datasets[item.datasetIndex].data[item.index];
            var label = data.datasets[item.datasetIndex].label + ": " + item.xLabel + "/" + item.yLabel;
            
            return label;
        },
        tooltipLabel(item, data) {
            var d = data.datasets[item.datasetIndex].data[item.index];
            var label = item.xLabel + "/" + item.yLabel;
            
            if(d.count > 1) {
                label += " (" + d.count + ")";
            }

            return label;
        },
        updatePoints_cloud() {
            var p = [];
            var s = [];
            var maxCount = 1;

            if(this.questionData && this.questionData.votes && this.questionData.votes.points) {
                for(var idx in this.questionData.votes.points) {
                    var o = this.questionData.votes.points[idx];
                    p.push(o);
                    s.push(o.count);
                    maxCount = Math.max(maxCount, o.count);
                }
            }

            for(var idx in s) {
                s[idx] = Math.max(1, 10 * (s[idx] / maxCount));
            }

            this.chartData.datasets[0].data = p;
            this.chartData.datasets[0].pointRadius = s;
            this.chartData.datasets[0].pointHoverRadius = s;
        },
        updatePoints_items() {
            var newDs = [];

            var idx = 0;
            for(var pidx in this.questionData.votes.points) {
                var point = this.questionData.votes.points[pidx];

                newDs.push({
                    label : point.title,
                    backgroundColor : this.itemColors[idx] ? this.itemColors[idx] : new Chance().color(),
                    data : [ point.point ],
                    pointRadius : 5,
                    pointHoverRadius: 5
                });

                idx++;
            }

            this.$set(this.chartData, "datasets", newDs);
        },
        updatePoints() {
            if(!this.questionData || !this.questionData.votes) return;

            if(!this.questionData.params.mode || (this.questionData.params.mode == 'cloud')) {
                this.updatePoints_cloud();
            } else {
                this.updatePoints_items();
            }

            if(this.$refs.scatterChart) {
                this.$refs.scatterChart.update();
            }
        },
        refreshSliders() {
            var me = this;

            for(var idx in me.$refs) {
                if(idx.startsWith('slider-')) {
                    var slider = me.$refs[idx];
                    if(slider.length) {
                        slider = slider[0];
                    }
                    if(slider && slider.refresh) {
                        slider.refresh();
                    } 
                }
            }
        },
        rangeValid(o) {
            if(!Number.isInteger(o.min)) return false;
            if(!Number.isInteger(o.max)) return false;

            if(o.min >= o.max) return false;

            return true;
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
            var rangeValid = function(o) {
                if(!Number.isInteger(o.min)) return false;
                if(!Number.isInteger(o.max)) return false;

                if(o.min >= o.max) return false;

                return true;
            };

            retval = retval && rangeValid(questionData.options[0]);
            retval = retval && rangeValid(questionData.options[1]);

            retval = retval && (questionData.options[0].title.length > 0);
            retval = retval && (questionData.options[1].title.length > 0);

            if(questionData.params.mode && (questionData.params.mode == 'items')) {
                retval = retval && (questionData.items.length > 0);
                for(var idx in questionData.items) {
                    retval = retval && (questionData.items[idx].title.length > 0);
                }
            }

            return retval;
        },
        newQuestionData : function() {
            return {
                type : "matrix",
                title : "",
                id : (++this.runningId),
                options : [
                    {
                        id : (++this.runningId),
                        min : 1,
                        max : 10,
                        title : "",
                        label : "X axis title"
                    },
                    {
                        id : (++this.runningId),
                        min : 1,
                        max : 10,
                        title : "",
                        label : "Y axis title"
                    }
                ],
                items : [],
                params : {
                    mode : "cloud"
                }
            };
        },
        getResult : function() {            
            return {
                ok : this.allOK,
                value : this.int_value
            }
        },
    },
    watch : {
        "questionData.items"(newV) {
            this.updateIntValue();
        },
        "questionData.params.mode"(newV) {
            this.updateIntValue();
        },
        "questionData.votes.points"(newV) {
            this.updatePoints();
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
        VueSlider,
        ScatterChart,
    }
}
</script>