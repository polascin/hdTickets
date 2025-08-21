'use client';

import { motion } from 'framer-motion';
import { Card } from '@/components/ui/Card';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import type { Team, Event, Sport } from '@/types';
import { Calendar, MapPin, Users, Trophy, TrendingUp } from 'lucide-react';

interface TeamCardProps {
  team: Team;
  upcomingEvents?: Event[];
  showStats?: boolean;
  variant?: 'compact' | 'detailed' | 'minimal';
  onClick?: () => void;
}

export function TeamCard({ 
  team, 
  upcomingEvents = [], 
  showStats = false, 
  variant = 'detailed',
  onClick 
}: TeamCardProps) {
  const getSportTheme = (sport: Sport) => {
    const themes = {
      NFL: 'from-blue-600 to-blue-800',
      NBA: 'from-orange-500 to-red-600',
      MLB: 'from-red-600 to-red-800',
      NHL: 'from-purple-600 to-indigo-700',
      MLS: 'from-green-600 to-green-800',
      NCAA: 'from-yellow-500 to-orange-600',
      OTHER: 'from-gray-600 to-gray-800',
    };
    return themes[sport.code] || themes.OTHER;
  };

  const upcomingCount = upcomingEvents.length;
  const nextGame = upcomingEvents[0];

  const cardVariants = {
    compact: "p-4",
    detailed: "p-6",
    minimal: "p-3"
  };

  return (
    <motion.div
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
      transition={{ duration: 0.2 }}
    >
      <Card 
        className={`${cardVariants[variant]} cursor-pointer hover:shadow-xl transition-all duration-300 overflow-hidden relative`}
        onClick={onClick}
      >
        {/* Background gradient based on team colors */}
        <div 
          className={`absolute inset-0 bg-gradient-to-br ${getSportTheme(team.sport)} opacity-5`}
          style={{
            background: `linear-gradient(135deg, ${team.colors.primary}10, ${team.colors.secondary}10)`
          }}
        />
        
        <div className="relative z-10">
          {/* Header */}
          <div className="flex items-start justify-between mb-4">
            <div className="flex items-center space-x-3">
              {team.logo && (
                <div className="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center overflow-hidden">
                  <img 
                    src={team.logo} 
                    alt={`${team.name} logo`}
                    className="w-10 h-10 object-contain"
                  />
                </div>
              )}
              <div>
                <h3 className="font-bold text-lg text-gray-900">
                  {variant === 'minimal' ? team.abbreviation : team.name}
                </h3>
                <p className="text-sm text-gray-600">{team.city}</p>
              </div>
            </div>
            
            <Badge 
              className={`text-white font-medium`}
              style={{ 
                backgroundColor: team.colors.primary,
                color: '#ffffff'
              }}
            >
              {team.sport.code}
            </Badge>
          </div>

          {variant !== 'minimal' && (
            <>
              {/* Venue Info */}
              {team.venue && (
                <div className="flex items-center text-sm text-gray-600 mb-3">
                  <MapPin className="w-4 h-4 mr-2" />
                  <span>{team.venue.name}</span>
                  <span className="mx-2">•</span>
                  <Users className="w-4 h-4 mr-1" />
                  <span>{team.venue.capacity.toLocaleString()}</span>
                </div>
              )}

              {/* Next Game */}
              {nextGame && (
                <div className="bg-gray-50 rounded-lg p-3 mb-3">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center text-sm">
                      <Calendar className="w-4 h-4 mr-2 text-blue-600" />
                      <span className="font-medium">Next Game</span>
                    </div>
                    <Badge variant="secondary" className="text-xs">
                      {new Date(nextGame.dateTime).toLocaleDateString()}
                    </Badge>
                  </div>
                  <p className="text-sm text-gray-600 mt-1">
                    vs {nextGame.homeTeam.id === team.id ? nextGame.awayTeam.name : nextGame.homeTeam.name}
                  </p>
                </div>
              )}

              {/* Stats */}
              {showStats && variant === 'detailed' && (
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div className="text-center">
                    <div className="text-2xl font-bold text-gray-900">{upcomingCount}</div>
                    <div className="text-xs text-gray-600">Upcoming Games</div>
                  </div>
                  <div className="text-center">
                    <div className="flex items-center justify-center">
                      <TrendingUp className="w-4 h-4 text-green-600 mr-1" />
                      <span className="text-lg font-bold text-gray-900">12-4</span>
                    </div>
                    <div className="text-xs text-gray-600">Season Record</div>
                  </div>
                </div>
              )}

              {/* Action Button */}
              {variant === 'detailed' && (
                <Button 
                  variant="outline" 
                  size="sm" 
                  className="w-full"
                  style={{ 
                    borderColor: team.colors.primary,
                    color: team.colors.primary 
                  }}
                >
                  View Tickets
                </Button>
              )}
            </>
          )}

          {/* Team colors indicator */}
          <div className="absolute top-0 right-0 w-4 h-4 rounded-bl-lg" style={{ backgroundColor: team.colors.primary }}>
            <div className="absolute top-0 right-0 w-2 h-2 rounded-bl-lg" style={{ backgroundColor: team.colors.secondary }}></div>
          </div>
        </div>
      </Card>
    </motion.div>
  );
}
