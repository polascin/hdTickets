import React from 'react';
import { Ticket } from '@shared/types';

interface TicketCardProps {
  ticket: Ticket;
  onSelect?: () => void;
}

const TicketCard: React.FC<TicketCardProps> = ({ ticket, onSelect }) => (
  <div onClick={onSelect} className="p-4 border rounded shadow hover:shadow-lg cursor-pointer">
    <h3 className="font-bold">{ticket.event_name}</h3>
    <p>{ticket.venue}</p>
    <p className="text-green-600 font-semibold">${ticket.price}</p>
  </div>
);

export default TicketCard;