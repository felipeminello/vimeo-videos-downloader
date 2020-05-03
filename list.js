require('dotenv').config({ path: require('path').resolve(process.cwd(), '.env') })

const fs = require('fs'),
  Vimeo = require('vimeo').Vimeo,
  client = new Vimeo(process.env.VIMEO_CLIENT_ID, process.env.VIMEO_CLIENT_SECRET, process.env.VIMEO_ACCESS_TOKEN)

const clientRequest = (page, perPage) => new Promise((resolve, reject) => {
  client.request({
    method: 'GET',
    path: `/me/videos`,
    query: {
      page,
      per_page: perPage
    }
  }, (err, body) => {
    if (err) {
      return reject(err)
    }

    return resolve(body)
  })
})

const requestVimeo = (page, perPage) => new Promise((resolve, reject) => {
  clientRequest(page, perPage).then(body => {
    if (!body.data || !body.data.length) {
      return reject('NO BODY DATA')
    }

    for (const data of body.data) {
      if (data.download && data.download.length) {
        const downData = data.download.sort((a, b) => {
          return b.size - a.size
        })[0]

        fs.appendFileSync(`./download.txt`, downData.link + '<===>' + data.uri.substr(data.uri.lastIndexOf('/') + 1) + '<===>' + downData.md5 + '<===>' + downData.size + '\n');
      } else {
        console.log('NÃO TEM data.download %s', data.uri)
      }
    }

    return resolve(true)
  }).catch(reject)
})

const run = async () => {
  const vimeoData = await clientRequest(1, 1)

  const total = vimeoData.total,
    perPage = 50,
    pages = Math.ceil((total / perPage))

  for (let page = 1; page <= pages; page++) {
    console.log('%s de %s', page, pages)

    await requestVimeo(page, perPage)
  }

  return true
}

run().then(() => {
  console.log('FINALIZOU')

  process.exit()
}).catch(err => {
  console.error(err)

  process.exit(1)
})

process.on('exit', () => {
  console.log('PROCESS EXIT')
})
