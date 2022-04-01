function dropCookies(match) {
    document.cookie.split(';')
    .map(v => v.split('='))
    .forEach(v => {
        var cookie = v[0].trim()
        if(cookie.match(match)) {
            document.cookie = `${cookie}= ; expires = Thu, 01 Jan 1970 00:00:00 GMT`
        }
    })
}
