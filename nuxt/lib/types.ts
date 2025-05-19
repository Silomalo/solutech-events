export interface OrganizationalType {
  id: number;
  tenant_name: string;
  tenant_domain: string;
  events: {
    id: number;
    title: string;
    venue: string;
    date: string;
    price: string;
    description?: string; // Added description field
    is_subscribed?: boolean;
  }[];
}
