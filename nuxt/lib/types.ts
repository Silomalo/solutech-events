
export interface OrganizationalType {
  id: number;
  tenant_name: string;
  tenant_domain: string;
  events: {
    id: number;
    venue: string;
    date: string;
    price: string;
  }[];
}
