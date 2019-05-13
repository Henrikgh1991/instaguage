<template>
        <div class="modal" tabindex="-1" role="dialog" data-keyboard="false">
                <div class="modal-dialog">
                        <div class="modal-content">
                                <div class="modal-header">
                                <!--
                                        <button type="button"
                                                class="close"
                                                data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span>
                                        </button>
                                -->
                                        <h4 class="modal-title">
                                                <slot name="title">Dialog title</slot>
                                        </h4>
                                </div>

                                <div class="modal-body">
                                        <slot name="content"></slot>
                                </div>

                                <div class="modal-footer">
                                        <slot name="footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </slot>
                                </div>
                        </div>
                </div>
        </div>
</template>

<style lang="sass" scoped>
</style>

<script>
import $ from 'jquery';
import bootstrap from 'bootstrap';

export default {
        name : "rootModal",
        methods : {
                hide : function() {
                        return $(this.$el).modal('hide');
                },
                show : function() {
                        return $(this.$el).modal({
                                backdrop : "static"
                        });
                }
        },
}
</script>
