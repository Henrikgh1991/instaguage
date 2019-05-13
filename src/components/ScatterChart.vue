<script>
import { Scatter, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
    extends: Scatter,
    mixins: [reactiveProp],    
    props: ['options'],
    mounted () {
        /*
        //this.renderChart(this.data, this.options);
        this.$watch(function() { return this.data.datasets[0].data; }, function(newV) {
            this.renderChart(this.data, this.options);
        });
        */
        this.renderChart(this.chartData, this.options);
    },
    methods : {
        update() {
            this.$data._chart.update();
        }
    }
}
</script>
