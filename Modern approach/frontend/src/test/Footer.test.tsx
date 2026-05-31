import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { Footer } from '../components/layout/Footer'
import type { ReactNode } from 'react'

const mockFetch = vi.fn()
globalThis.fetch = mockFetch

function Wrapper({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
}

beforeEach(() => {
  mockFetch.mockReset()
})

describe('Footer', () => {
  it('renders copyright text', () => {
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ version: '1.0.0', api_version: 'v1', name: 'PedalPal API' }) })
    render(<Footer />, { wrapper: Wrapper })
    expect(screen.getByText(/PedalPal/)).toBeInTheDocument()
    expect(screen.getByText(/2008/)).toBeInTheDocument()
  })

  it('shows api version after successful fetch', async () => {
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ version: '1.0.0', api_version: 'v1', name: 'PedalPal API' }) })
    render(<Footer />, { wrapper: Wrapper })
    expect(await screen.findByText(/v1/)).toBeInTheDocument()
    expect(await screen.findByText(/1\.0\.0/)).toBeInTheDocument()
  })

  it('gracefully handles version fetch failure', async () => {
    mockFetch.mockRejectedValue(new Error('Network error'))
    render(<Footer />, { wrapper: Wrapper })
    expect(screen.getByText(/PedalPal/)).toBeInTheDocument()
  })
})
