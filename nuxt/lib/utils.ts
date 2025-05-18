export function generateUrl(path: string): string {
  const host_url = useRequestURL();
  // Split the origin at "//" to get protocol and domain parts
  const [protocol, domain] = host_url.origin.split('//');
  // Clean up the path - remove any leading/trailing slashes
  const cleanPath = path.replace(/^\/+|\/+$/g, '');
  // Construct the URL with path as subdomain: protocol//path.domain
  return `${protocol}//${cleanPath}.${domain}`;
}
export function getSubdomain(){
    const host_url = useRequestURL();
    const subdomain = host_url.hostname.split('.')[0];
    // Check if the subdomain is not 'www' or 'localhost'
    const is_set= subdomain !== 'www' && subdomain !== 'localhost';
    if (is_set) {
        return subdomain;
    }
    return null;
    
}