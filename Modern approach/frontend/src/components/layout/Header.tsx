import { Link } from '@tanstack/react-router'
import { Bike } from 'lucide-react'

interface HeaderProps {
  subtitle?: string
}

export function Header({ subtitle }: HeaderProps) {
  return (
    <header role="banner" className="text-center mb-12">
      <Link to="/" className="no-underline" aria-label="PedalPal Home">
        <h1 className="text-5xl font-bold text-white drop-shadow-lg mb-2">
          <Bike className="inline size-10 mr-2" aria-hidden="true" />
          PedalPal Bike Rentals
        </h1>
      </Link>
      {subtitle && <p className="text-lg text-white/80" aria-roledescription="tagline">{subtitle}</p>}
    </header>
  )
}
