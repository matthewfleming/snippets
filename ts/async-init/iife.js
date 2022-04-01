console.log('--- iife ---')
var init = (async () => {
    //init the things
    console.log('init')
    return 1
})()

async function handler() {
    var i = 2
    while(i--) {
        console.log('awaiting')
        console.log(`config: ${await init}`)
    }
}

handler().then(() => console.log('done'))
