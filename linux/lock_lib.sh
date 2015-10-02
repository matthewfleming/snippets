open_file_descriptor() {
    exec 3<>filename
}

close_file_descriptor() {
    exec 3>&-
}

list_file_descriptors() {
    ls /proc/$$/fd/
}