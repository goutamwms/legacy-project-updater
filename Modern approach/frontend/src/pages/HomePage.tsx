import { Link } from '@tanstack/react-router'
import { Header } from '../components/layout/Header'
import { Footer } from '../components/layout/Footer'
import { Button } from '../components/ui/button'
import { useResetData } from '../hooks/useBikes'
import { Toast } from '../components/ui/toast'
import { useState, useCallback } from 'react'

export function HomePage() {
  const resetData = useResetData()
  const [toastVisible, setToastVisible] = useState(false)
  const [toastMsg, setToastMsg] = useState('')

  const showToast = useCallback((msg: string) => {
    setToastMsg(msg)
    setToastVisible(true)
  }, [])

  const handleReset = useCallback(async () => {
    try {
      await resetData.mutateAsync()
      showToast('All data reset!')
    } catch {
      showToast('Reset failed.')
    }
  }, [resetData, showToast])

  return (
    <div className="min-h-screen bg-linear-135 from-indigo-400 to-purple-500 flex flex-col items-center px-5 py-10 animate-fade-in">
      <Header />

      <section aria-label="Choose your bike type" className="flex flex-wrap justify-center gap-8 max-w-3xl">
        <Link
          to="/beach"
          className="no-underline animate-slide-up opacity-0 [animation-fill-mode:forwards]"
          style={{ animationDelay: '100ms' }}
          aria-label="Browse beach cruisers"
        >
          <div className="w-80 rounded-2xl bg-white p-10 text-center shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-3xl cursor-pointer">
            <div className="text-5xl mb-4" aria-hidden="true">🏖️</div>
            <h2 className="text-2xl font-bold text-gray-800 mb-3">Beach Cruisers</h2>
            <p className="text-sm text-gray-500 mb-6">
              Laid-back frames, wide tires, and enough style to make the seagulls jealous.
            </p>
            <span className="inline-block rounded-full bg-linear-135 from-pink-400 to-rose-400 px-8 py-3 font-semibold text-white">
              Browse Beach Cruisers
            </span>
          </div>
        </Link>

        <Link
          to="/mountain"
          className="no-underline animate-slide-up opacity-0 [animation-fill-mode:forwards]"
          style={{ animationDelay: '200ms' }}
          aria-label="Browse mountain bikes"
        >
          <div className="w-80 rounded-2xl bg-white p-10 text-center shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-3xl cursor-pointer">
            <div className="text-5xl mb-4" aria-hidden="true">⛰️</div>
            <h2 className="text-2xl font-bold text-gray-800 mb-3">Mountain Bikes</h2>
            <p className="text-sm text-gray-500 mb-6">
              Suspension forks, aggressive geometry, and enough gears to confuse anyone.
            </p>
            <span className="inline-block rounded-full bg-linear-135 from-blue-400 to-cyan-300 px-8 py-3 font-semibold text-white">
              Browse Mountain Bikes
            </span>
          </div>
        </Link>

        <Link
          to="/electric"
          className="no-underline animate-slide-up opacity-0 [animation-fill-mode:forwards]"
          style={{ animationDelay: '300ms' }}
          aria-label="Browse electric bikes"
        >
          <div className="w-80 rounded-2xl bg-white p-10 text-center shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:shadow-3xl cursor-pointer">
            <div className="text-5xl mb-4" aria-hidden="true">⚡</div>
            <h2 className="text-2xl font-bold text-gray-800 mb-3">Electric Bikes</h2>
            <p className="text-sm text-gray-500 mb-6">
              Zero emissions, full torque. The future of commuting is quietly fast.
            </p>
            <span className="inline-block rounded-full bg-linear-135 from-purple-500 to-violet-600 px-8 py-3 font-semibold text-white">
              Browse Electric Bikes
            </span>
          </div>
        </Link>
      </section>

      <Button
        variant="ghost"
        size="icon"
        onClick={handleReset}
        loading={resetData.isPending}
        className="mt-12 text-3xl transition-transform hover:scale-110 active:scale-90"
        aria-label="Reset all data to defaults"
      >
        🚲
      </Button>

      <Footer />

      <Toast message={toastMsg} visible={toastVisible} onClose={() => setToastVisible(false)} />
    </div>
  )
}
