import { useVersion } from '../../hooks/useVersion'

export function Footer() {
  const { data } = useVersion()

  return (
    <footer role="contentinfo" className="mt-16 text-center text-white/60 text-xs">
      <small>
        PedalPal &copy; 2008. All bikes should be real. All prices are made up.
        {data && <> &middot; {data.api_version} ({data.version})</>}
      </small>
    </footer>
  )
}
