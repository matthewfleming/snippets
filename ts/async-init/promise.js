console.log('--- promise ---')
var init = new Promise(async (resolve) => {
    //init the things
    console.log('init')
    resolve(1)
})

async function handler() {
    var i = 2
    while(i--) {
        console.log('awaiting')
        console.log(`config: ${await init}`)
    }
}

handler().then(() => console.log('done'))
