

error() {
        echo "$@" >&2
}


cmd_exec() {
        echo "$@"
        $@
        if [ $? -ne 0 ]; then
                error "$@"
                #exit 1
        fi
}