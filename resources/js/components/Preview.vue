<template>
    <div>
        <heading class="mb-6">Data Importer</heading>

        <card class="flex flex-col">
            <div class="p-8">
                <div v-if="loading" class="w-1/3">
                    <h2 class="pb-4">Loading</h2>
                    <p class="pb-4">
                        We are currently loading the sample data from your provided file. If this is a large file, this could take up to 5 minutens. Please bare with us.
                    </p>
                    <p class="pb-4 loader-container" style="display: inline-block;">
                        <span class="loader">Loading...</span>
                        Preparing sample data
                    </p>
                </div>
                <div v-else-if="importing" class="w-1/3">
                    <h2 class="pb-4">Importing</h2>
                    <p class="pb-4">
                        We are currently importing your data. If this is a large file, this could take up to 5 minutens. Please bare with us.
                    </p>
                    <p class="pb-4 loader-container" style="display: inline-block;">
                        <span class="loader">Loading...</span>
                        {{ import_status_message }}
                    </p>
                    <div id="progressbar">
                       <div id="progressbar-progress"></div>
                       <span>{{ import_status_progress_message }}</span>
                    </div>
                </div>
                <div v-else>
                    <h2 class="pb-4">Preview</h2>
                    <p class="pb-4">
                        We were able to discover <b>{{ headings.length }}</b> column(s) and <b>{{ total_rows }}</b>
                        row(s) in your data.
                    </p>
                    <p class="pb-4">
                        Choose a resource to import them into and match up the headings from the CSV to the
                        appropriate fields of the resource.
                    </p>

                    <h2 class="py-4">Resource</h2>
                    <p class="pb-4">Choose which resource to import your data into:</p>
                    <div>
                        <select name="resource" class=" form-control form-select" v-model="resource">
                            <option value="">- Select a resource -</option>
                            <option v-for="(label, index) in resources" :value="index">{{ label }}</option>
                        </select>
                        <select name="unique" class=" form-control form-select" v-model="unique">
                            <option selected="selected" value="">- Match existing by -</option>
                            <option v-for="field in fields[resource]" :value="field.attribute">{{ field.name }}</option>
                        </select>
                    </div>

                    <div v-if="errors && errors.length > 0">
                        <h2 class="py-4 text-danger">Error</h2>
                        <p class="pb-4 text-danger">There was an error. Please check the error message(s) below:</p>
                        <p class="block text-danger text-sm mb-3" v-for="error in errors">
                            {{ error }}
                        </p>
                    </div>
                    <div v-if="failures && failures.length > 0">
                        <h2 class="py-4 text-danger">
                            Validation error
                        </h2>
                        <p class="pb-4 text-danger">
                            We found these validation error's. Please note, we only show up to five validation errors on this list:
                        </p>
                        <p class="block text-danger text-sm mb-3" v-for="(failure, index) in failures" v-if="index < 1">
                            Error on row number "{{ failure.row }}" with the following message "{{ failure.errors[0] }}".
                        </p>
                    </div>
                </div>
            </div>

            <table v-if="!loading && !importing" class="table w-full">
                <thead>
                    <tr>
                        <th v-for="heading in headings">{{ heading }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td v-for="heading in headings" class="text-center">
                            <select class="w-full form-control form-select" v-model="mappings[heading]">
                                <option value="">- Ignore this column -</option>
                                <option v-for="field in fields[resource]" :value="field.attribute">{{ field.name }}</option>
                            </select>
                        </td>
                    </tr>
                    <tr v-for="(row, index) in rows" :data-index="index" v-bind:class="{ 'has-error': rowHasError(index) }">
                        <td v-for="(heading, columnIndex) in headings">
                            {{ row[heading] }}
                            <span v-if="colHasError(index, heading)" class="block text-danger text-sm">
                                {{ colHasError(index, heading) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!loading && !importing" class="bg-30 flex px-8 py-4">
                <button class="btn btn-default btn-primary" @click="runImport" :disabled="disabledImport">
                    Import &rightarrow;
                </button>
            </div>
        </card>
    </div>
</template>

<script>
import Echo from "laravel-echo"
import Pusher from "pusher-js"

export default {
    mounted() {
        const self = this;

        Nova.request()
            .get('/nova-vendor/nova-data-importer/config')
            .then(function (response) {
                
                self.config = response.data

                if (response.data.use_jobs) {

                    if (response.data.pusher_log_to_console) {
                        Pusher.logToConsole = true;
                    }

                    window.Echo = new Echo({
                      broadcaster: 'pusher',
                      key: self.config.pusher_key,
                      cluster: 'eu',
                      forceTLS: true
                    });


                    var channel = window.Echo.channel('myChannel');
                    channel.listen('.server.created', function(data) {
                        console.log(data)
                        self.parsePreviewData(data)
                    });
                    channel.listen('.server.error', function(data) {
                        self.handleImportResponse(data)
                    });
                    channel.listen('.server.success', function(data) {
                        self.handleImportResponse(data)
                    });
                }
            });

        Nova.request()
            .get('/nova-vendor/nova-data-importer/preview/' + this.file)
            .then(function (response) {
                if (response.data) {
                    self.parsePreviewData(response.data)
                }
            });
    },
    data() {
        return {
            headings: [],
            rows: [],
            config: {},
            resources: [],
            fields: [],
            resource: '',
            unique: '',
            mappings: {},
            errors: false,
            failures: false,
            loading: true,
            importing: false,
            import_status_message: 'Preparing your import',
            import_status_progress_message: '',
            import_progress_interval: null
        };
    },
    props: [
        'file'
    ],
    watch: {
        resource : function (resource) {
            const self = this;

            // Reset all of the headings to blanks
            this.headings.forEach(function (heading) {
                self.$set(self.mappings, heading, "");
            });

            if (resource === "") {
                return;
            }

            // For each field of the resource, try to find a matching heading and pre-assign
            this.fields[resource].forEach(function (field_config) {
                let field = field_config.attribute,
                    heading_index = self.headings.indexOf(field);

                if (heading_index < 0) {
                    return;
                }

                let heading = self.headings[heading_index];

                if (heading === field) {
                    self.$set(self.mappings, heading, field);
                }
            });
        }
    },
    methods: {

        handleImportResponse: function (response) {
            const self = this
            if (response) {
                if (self.import_progress_interval) {
                    clearInterval(self.import_progress_interval)
                }

                if (response.result === 'success') {
                    self.$router.push({name: 'csv-import-review', params: {file: self.file, resource: self.resource}});
                } else {
                    self.importing = false
                    self.$toasted.show('There were problems importing some of your data', {type: "error"});
                    self.errors = response.errors
                    self.failures = response.failures
                }
            }
        },

        parsePreviewData: function (data) {
            const self = this;
            self.loading = false

            self.headings = data.headings;
            self.rows = data.sample;
            self.resources = data.resources;
            self.total_rows = data.total_rows;
            self.fields = data.fields;

            self.headings.forEach(function (heading) {
                self.$set(self.mappings, heading, "");
            });
        },

        rowHasError: function (index) {
            var row_count = index + 1

            for (var error in this.errors) {
                if (this.errors[error].row == row_count) {
                    return true
                }
            }
            return false;
        },

        colHasError: function (index, import_column) {

            for (var mapping in this.mappings) {
                if (mapping == import_column) {
                    var column = this.mappings[mapping]
                    break
                }
            }

            var row_count = index + 1

            for (var failure in this.failures) {
                if (this.failures[failure].row != row_count) {
                    continue
                }
                if (this.failures[failure].attribute != column) {
                    continue
                }

                return this.failures[failure].errors[0]
            }

            return false;
        },

        runImport: function () {
            const self = this;

            if (! this.hasValidConfiguration()) {
                return;
            }
            self.importing = true
            let data = {
                resource: this.resource,
                mappings: this.mappings,
                unique: this.unique
            };

            self.import_progress_interval = setInterval(function(){ 
                
                Nova.request()
                    .get('/nova-vendor/nova-data-importer/progress/' + self.file)
                    .then(function (response) {
                        self.import_status_message = response.data.status
                        self.import_status_progress_message = response.data.progress_message
                        document.getElementById("progressbar-progress").style.width = response.data.progress + '%';
                    });

            }, 10000);

            Nova.request()
                .post(this.url('import/' + this.file), data)
                .then(function (response) {
                    if (!self.config.use_jobs) {
                        if (self.import_progress_interval) {
                            clearInterval(self.import_progress_interval)
                        }
                    }
                    self.handleImportResponse(response.data)
                }).catch(response => {
                    if (self.import_progress_interval) {
                        clearInterval(self.import_progress_interval)
                    }
                    self.importing = false
                });
        },
        hasValidConfiguration: function () {
            const mappedColumns = [],
                mappings = this.mappings;

            Object.keys(mappings).forEach(function (key) {
                if (mappings[key] !== "") {
                    mappedColumns.push(key);
                }
            });

            return this.resource !== '' && mappedColumns.length > 0;
        },
        url: function (path) {
            return '/nova-vendor/nova-data-importer/' + path;
        }
    },
    computed: {
        disabledImport: function () {
            return ! this.hasValidConfiguration();
        },
    }
}
</script>

<style>
.loader-container {
    line-height: 1em;
    border: 2px solid var(--success);
    border-radius: .5rem;
    padding: 10px;
}
.loader,
.loader:after {
  border-radius: 50%;
    width: 2em;
    height: 2em;
}
.loader {
    display: inline-block;
    margin-right: 1em;
  font-size: 10px;
  position: relative;
  text-indent: -9999em;
  border-top: 0.3em solid rgba(238,241,244, 1);
  border-right: 0.3em solid rgba(238,241,244, 1);
  border-bottom: 0.3em solid rgba(238,241,244, 1);
  border-left: 0.3em solid var(--success);

  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
  -webkit-animation: load8 1.1s infinite linear;
  animation: load8 1.1s infinite linear;
}
@-webkit-keyframes load8 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes load8 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

#progressbar {
    background-color: #eef1f4;
    border-radius: 13px; /* (height of inner div) / 2 + padding */
    padding: 3px;
    margin-top: 20px;
    position: relative;
}

#progressbar>div {
    background-color: #21b978;
    width: 0%; /* Adjust with JavaScript */
    height: 20px;
    border-radius: 10px;
}
#progressbar>span {
    position: absolute;
    top: 7px;
    font-size: 12px;
    left: 9px;
}

</style>
