import { RouterProvider, createRouter, createRootRoute, createRoute } from '@tanstack/react-router'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { HomePage } from './pages/HomePage'
import { BikeListPage } from './pages/BikeListPage'
import { ErrorBoundary, ErrorFallback } from './components/ErrorBoundary'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { staleTime: 30_000, retry: 1 },
  },
})

const rootRoute = createRootRoute()

const homeRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
  component: () => <HomePage />,
})

const beachRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/beach',
  component: () => <BikeListPage bikeType="beach" />,
})

const mountainRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/mountain',
  component: () => <BikeListPage bikeType="mountain" />,
})

const electricRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/electric',
  component: () => <BikeListPage bikeType="electric" />,
})

function CrashPage() {
  throw new Error('This is a simulated crash for testing the ErrorBoundary. Please click "Try Again" to reset the state and return to safety.')
}

const debugErrorRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/debug/error',
  component: CrashPage,
})

const routeTree = rootRoute.addChildren([homeRoute, beachRoute, mountainRoute, electricRoute, debugErrorRoute])

const router = createRouter({
  routeTree,
  defaultErrorComponent: ({ error, reset }) => (
    <ErrorFallback error={error} onReset={reset} />
  ),
})

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <main id="main-content">
        <ErrorBoundary>
          <RouterProvider router={router} />
        </ErrorBoundary>
      </main>
    </QueryClientProvider>
  )
}
