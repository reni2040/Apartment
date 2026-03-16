import Link from "next/link";

export default function Home() {
  return (
    <main className="min-h-screen bg-neutral-950 text-white">
      <nav className="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-neutral-950/80 border-b border-neutral-800">
        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500" />
            <span className="font-semibold text-lg">NextKit</span>
          </div>
          <div className="flex items-center gap-8">
            <Link href="#" className="text-neutral-400 hover:text-white transition-colors text-sm">Features</Link>
            <Link href="#" className="text-neutral-400 hover:text-white transition-colors text-sm">Docs</Link>
            <Link href="#" className="text-neutral-400 hover:text-white transition-colors text-sm">Templates</Link>
            <button className="px-4 py-2 bg-white text-neutral-950 rounded-lg text-sm font-medium hover:bg-neutral-200 transition-colors">
              Get Started
            </button>
          </div>
        </div>
      </nav>

      <section className="pt-32 pb-20 px-6">
        <div className="max-w-4xl mx-auto text-center">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-900 border border-neutral-800 text-xs text-neutral-400 mb-8">
            <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
            Now with App Router & React 19
          </div>
          <h1 className="text-5xl md:text-7xl font-bold tracking-tight mb-6">
            Build faster with{" "}
            <span className="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-400 bg-clip-text text-transparent">
              NextKit
            </span>
          </h1>
          <p className="text-xl text-neutral-400 max-w-2xl mx-auto mb-10">
            A minimal Next.js starter template designed for AI-assisted development. 
            Start building your next idea in seconds.
          </p>
          <div className="flex items-center justify-center gap-4">
            <button className="px-6 py-3 bg-white text-neutral-950 rounded-xl font-medium hover:bg-neutral-200 transition-colors">
              Start Building
            </button>
            <button className="px-6 py-3 bg-neutral-900 text-white rounded-xl font-medium border border-neutral-800 hover:border-neutral-700 transition-colors">
              View on GitHub
            </button>
          </div>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="max-w-6xl mx-auto">
          <div className="grid md:grid-cols-3 gap-6">
            {[
              { title: "App Router", desc: "Full support for Next.js 14+ App Router with server components", icon: "◈" },
              { title: "TypeScript", desc: "Type-safe codebase with strict TypeScript configuration", icon: "◇" },
              { title: "Tailwind CSS 4", desc: "Utility-first CSS framework with modern features", icon: "◆" },
            ].map((item, i) => (
              <div key={i} className="p-6 rounded-2xl bg-neutral-900/50 border border-neutral-800 hover:border-neutral-700 transition-colors">
                <span className="text-2xl text-violet-400">{item.icon}</span>
                <h3 className="mt-4 font-semibold text-lg">{item.title}</h3>
                <p className="mt-2 text-neutral-400 text-sm">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20 px-6 border-t border-neutral-900">
        <div className="max-w-6xl mx-auto">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl font-bold mb-4">Built for modern development</h2>
              <p className="text-neutral-400 mb-6">
                Everything you need to start building production-ready applications. 
                Optimized for performance and developer experience.
              </p>
              <ul className="space-y-3">
                {["Server Components by default", "Streaming & Suspense", "Route Handlers API", "Middleware support"].map((item, i) => (
                  <li key={i} className="flex items-center gap-3 text-neutral-300">
                    <span className="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs">✓</span>
                    {item}
                  </li>
                ))}
              </ul>
            </div>
            <div className="p-6 rounded-2xl bg-neutral-900 border border-neutral-800 font-mono text-sm">
              <div className="flex gap-2 mb-4">
                <span className="w-3 h-3 rounded-full bg-red-500" />
                <span className="w-3 h-3 rounded-full bg-yellow-500" />
                <span className="w-3 h-3 rounded-full bg-green-500" />
              </div>
              <pre className="text-neutral-300">
{`// Server Component
export default async function Page() {
  const data = await fetchData();
  
  return (
    <div className="p-4">
      <h1>{data.title}</h1>
    </div>
  );
}`}
              </pre>
            </div>
          </div>
        </div>
      </section>

      <footer className="py-12 px-6 border-t border-neutral-900">
        <div className="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <div className="w-6 h-6 rounded-md bg-gradient-to-br from-violet-500 to-fuchsia-500" />
            <span className="font-medium">NextKit</span>
          </div>
          <p className="text-neutral-500 text-sm">Built with Next.js & Tailwind CSS</p>
        </div>
      </footer>
    </main>
  );
}
