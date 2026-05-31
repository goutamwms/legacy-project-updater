import { Component } from 'react'
import type { ErrorInfo, ReactNode } from 'react'
import { AlertTriangle, RefreshCw } from 'lucide-react'

interface ErrorBoundaryProps {
  children: ReactNode
}

interface ErrorBoundaryState {
  error: Error | null
}

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  state: ErrorBoundaryState = { error: null }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { error }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[ErrorBoundary] Caught:', error, info)
  }

  handleReset = () => {
    this.setState({ error: null })
  }

  render() {
    if (this.state.error) {
      return <ErrorFallback error={this.state.error} onReset={this.handleReset} />
    }
    return this.props.children
  }
}

export function ErrorFallback({ error, onReset }: { error: Error; onReset: () => void }) {
  return (
    <div className="min-h-screen bg-linear-135 from-red-400 to-rose-500 flex items-center justify-center p-8">
      <div className="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-md w-full p-10 text-center">
        <div className="size-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
          <AlertTriangle className="size-8 text-red-500" aria-hidden="true" />
        </div>

        <h1 className="text-2xl font-bold text-gray-800 mb-2">Unexpected Error</h1>
        <p className="text-gray-500 mb-6">
          Something went wrong while rendering this page. Please try again.
        </p>

        <div className="bg-gray-50 rounded-xl p-4 mb-8 text-left">
          <p className="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Error details</p>
          <p className="text-sm text-gray-600 font-mono break-all">{error.message}</p>
        </div>

        <button
          onClick={onReset}
          className="inline-flex items-center gap-2 px-6 py-3 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 active:bg-red-700 transition-all cursor-pointer"
        >
          <RefreshCw className="size-4" aria-hidden="true" />
          Try Again
        </button>
      </div>
    </div>
  )
}
