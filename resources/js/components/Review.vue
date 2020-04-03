<template>
    <div>
        <heading class="mb-6">Data Importer</heading>
        <card class="flex flex-col items-center justify-center" style="min-height: 300px">
            <h1 class="pb-4">Done!</h1>
            <p class="pb-4">All your data was successfully imported.</p>
            <a href="/marshmallow/csv-import/">Upload more</a>
        </card>
    </div>
</template>

<script>
export default {
    mounted() {
        const self = this;
        this.$toasted.show('All data imported!', {type: "success"});
        Nova.request()
            .delete('/nova-vendor/nova-data-importer/' + self.file);
    },
    data() {
        return {
            imported: '',
        };
    },
    props: [
        'file'
    ],
    methods: {
    }
}
</script>

<style>
/* Scoped Styles */
</style>
