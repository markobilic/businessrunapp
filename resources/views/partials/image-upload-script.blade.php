<script>
    function imageUpload() {
        return {
            isDropping: false,
            isUploading: false,
            progress: 0,
            supportedFormats: [                  
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/apng'
            ],
            handleImageSelect(event) {  
                if (event.target.files.length) {
                    this.uploadImagesIfSupported(event.target.files);
                    this.$refs.fileInput.value = '';
                }
            },
            handleImageDrop(event) { 
                if (event.dataTransfer.files.length > 0) {
                    this.uploadImagesIfSupported(event.dataTransfer.files);
                    this.$refs.fileInput.value = '';
                }
            }, 
            uploadImagesIfSupported(file) {
                if (file.length > 1) {
                    alert('You can upload a maximum of 1 image.');
                    return;
                }
                for (let i = 0; i < file.length; i++) {
                    if (!this.supportedFormats.includes(file[i].type)) {
                        alert(`Unsupported file format: ${file[i].name}`);
                        return;
                    }
                    
                    if (file[i].size > 16 * 1024 * 1024) {
                        alert(`File size exceeds 16MB: ${file[i].name}`);
                        return;
                    }
                }
                this.uploadImageFiles(file);
            },
            uploadImageFiles(file) {
                const $this = this;
                this.isUploading = true;
                @this.upload('file', file[0],
                    function (success) {
                        $this.isUploading = false;
                        $this.progress = 0;
                    },
                    function(error) {
                        console.log('error', error);
                    },
                    function (event) {
                        $this.progress = event.detail.progress;
                    }
                )
            },
            removeImageUpload(filename) { 
                @this.removeUpload('file', filename);
            }, 
        }
    };
</script>
