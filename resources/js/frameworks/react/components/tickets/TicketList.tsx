import React from 'react';
import { TicketListProps } from '@shared/types';

const TicketList: React.FC<TicketListProps> = ({ tickets, loading, error, onTicketSelect }) => {
  if (loading) return <div>Loading tickets...</div>;
  if (error) return <div>Error: {error}</div>;
  
  return (
    <div className="space-y-4">
      {tickets.map(ticket => (
        <div key={ticket.id} onClick={() => onTicketSelect?.(ticket)} className="p-4 border rounded cursor-pointer hover:bg-gray-50">
          <h3>{ticket.event_name}</h3>
          <p>{ticket.venue} - ${ticket.price}</p>
        </div>
      ))}
    </div>
  );
};

export default TicketList;