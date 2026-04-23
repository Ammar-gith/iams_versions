@extends('layouts.masterVertical')
@push('content')
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs" />

    <div id="test-app">
        <file-uploader collection="test" label="Test Upload" v-model="tokens"></file-uploader>
        {{-- <p>Tokens: {{ tokens }}</p> --}}
    </div>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-file-uploader"></script>
    <script>
        new Vue({
            el: '#test-app',

        });
    </script>
@endpush

<!-- resources/views/test-upload.blade.php -->
<form method="POST" action="/test-upload" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
