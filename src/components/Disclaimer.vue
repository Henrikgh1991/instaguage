<template>
    <modal ref="disclaimerDlg">
        <span slot="title">Usage information</span>
        <div slot="content" class="disclaimer-content">
            <ul>
                <li>
                The results of your survey will be <strong>viewable by anyone</strong> with the URL/participation code - it’s not possible to hide results 
                </li>
                <li>
                Please only include ‘<strong>unrestricted</strong>’ information in your survey
                </li>
            </ul>

            <p>
                If InstaGague doesn’t support your requirements, please find alternative survey tools available at 
                <a href="https://siemens.socialcast.com/messages/2650044"
                    target="_blank">
                    <font-awesome-icon icon="external-link-alt" />
                    https://siemens.socialcast.com/messages/2650044</a>.
            </p>
        </div>
        <div slot="footer">
            <button type="button"
                class="btn btn-danger reject-button"
                @click.stop="reject"
                title="Leave!">
                Reject
            </button>

            <div class="form-check">
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" v-model="understood" :value="true">
                    I have read and understood this information
                </label>
            </div>
            
            <button type="button"
                class="btn btn-primary"
                v-if="understood"
                @click.stop="ack"
                title="OK">
                Acknowledge
            </button>
        </div>
    </modal>
</template>

<style lang="scss" scoped>
.form-check {
    display:inline-block;
    line-height:33px;
}

a {
    white-space:nowrap;
}

.reject-button {
    float:left;
}

h5 {
    font-weight:bold;
}

.disclaimer-content {
    //max-height:20rem;
    overflow-y:auto;
}
</style>

<script>
import Modal from './Modal.vue'

export default {
    name : "Disclaimer",
    data : function() {
        return {
            understood : false,
            id : "usage-information-1",
            acknowledgedDisclaimers : { },
        };
    },
    created : function() {
        if(this.$route.query.resetDisclaimers !== undefined) {
            window.localStorage.removeItem("acknowledgedDisclaimers");
        }

        var storedAcks = window.localStorage.getItem("acknowledgedDisclaimers");
        if(storedAcks != null) {
            this.acknowledgedDisclaimers = JSON.parse(storedAcks);
        }
    },
    mounted : function() {
        if(!this.alreadyAcknowledged) {
            this.$refs.disclaimerDlg.show();
        }
    },
    methods : {
        reject() {
            this.$refs.disclaimerDlg.hide();
            this.$router.push({ name : 'Home' })
        },
        ack() {
            this.$refs.disclaimerDlg.hide();
            this.$set(this.acknowledgedDisclaimers, this.id, true);
        },
    },
    computed : {
        alreadyAcknowledged() {            
            if(this.acknowledgedDisclaimers[this.id] === true) {
                return true;
            }
            return false;
        },
    },
    watch : {
        acknowledgedDisclaimers(newV) {
            window.localStorage.setItem("acknowledgedDisclaimers", JSON.stringify(newV));
        },
    },
    components : {
        Modal,
    }
}
</script>