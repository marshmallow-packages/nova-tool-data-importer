<template>
    <div>
        <heading class="mb-6">Data Importer</heading>

        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <h1 class="pb-4">Start your upload</h1>
            <p class="pb-4">Please select your file. Once you've selected your file, we will start the import immediately.</p>
            <div v-bind:class="{ 'opacity-05': file_upload_status }">
                <input class="custom-file-input" type="file" name="file" ref="file" @change="handleFile">
            </div>
            <p v-if="file_upload_status" class="pb-4 loader-container">
                <span class="loader">Loading...</span>
                {{ file_upload_status }}
            </p>
            <p v-if="file_upload_error" class="text-white-50% text-lg mt-6">
                <code class="ml-1 border border-80 text-sm font-mono text-white bg-black rounded px-2 py-1">
                    {{ file_upload_error }}
                </code>
            </p>
        </card>
    </div>
</template>

<script>
export default {
    mounted() {
        //
    },
    data() {
        return {
            file: '',
            file_upload_error: null,
            file_upload_status: null
        };
    },
    methods: {
        uploadLoader: function (status) {
            this.file_upload_status = status
        },
        handleFile: function (event) {
            this.loading = false
            this.file = this.$refs.file.files[0];
            this.upload()
        },
        upload: function (event) {
            let formData = new FormData();

            // send it to the server
            formData.append('file', this.file);

            const self = this;
            self.uploadLoader('Upload started')
            self.file_upload_error = null
            // if it's valid, move to the next screen
            Nova.request()
                .post('/nova-vendor/nova-data-importer/upload',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        onUploadProgress: function(progressEvent) {
                          var percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                          self.uploadLoader('Upload at ' + percentCompleted + '%')
                          if (percentCompleted == 100) {
                            self.uploadLoader('Creating preview')
                          }
                        }
                    }
                ).then(function(response){
                    self.uploadLoader('Redirecting to preview')
                    self.$router.push({name: 'csv-import-preview', params: {file: response.data.file}})
                })
                .catch(function(e){
                    self.uploadLoader(false)
                    self.$toasted.show(e.response.data.message, {type: "error"});

                    if (e.response.data.errors) {
                        self.file_upload_error = e.response.data.errors['file'][0]
                    } else {
                        self.file_upload_error = e
                    }
                });
        }
    }
}
</script>

<style>
/* Scoped Styles */
.opacity-05 {
    opacity: .5
}
.custom-file-input {
    color: transparent;
    width: 248px;
    height: 36px;
    border-radius: .5rem;
}
.custom-file-input::-webkit-file-upload-button {
    visibility: hidden;
}
.custom-file-input::before {
    content: 'Select and start uploading';
    display: inline-block;
    background-color: var(--success);
    color: var(--white);
    outline: none;
    white-space: nowrap;
    -webkit-user-select: none;
    cursor: pointer;
    height: 2.25rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    line-height: 2.25rem;
    border-radius: .5rem;
    -webkit-box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
    box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
    text-shadow: 0 1px 2px rgba(0,0,0,.2);
    display: inline-block;
    text-decoration: none;
    font-weight: 800;
    font-family: inherit;
}
.custom-file-input:hover::before {
  border-color: black;
}
.custom-file-input:active {
  outline: 0;
}
.custom-file-input:active::before {
  background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9); 
}

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
</style>
