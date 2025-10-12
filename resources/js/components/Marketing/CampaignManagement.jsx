import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Textarea } from "@/components/ui/textarea";
import {
  BarChart3,
  Mail,
  MessageSquare,
  Pause,
  Play,
  Plus,
  Smartphone,
  Square,
  Target,
  Users
} from 'lucide-react';
import { useEffect, useState } from 'react';

/**
 * Campaign Management Component
 * 
 * Complete campaign management interface featuring:
 * - Campaign creation and editing
 * - Multi-channel campaign support
 * - Campaign lifecycle management
 * - Performance monitoring
 * - Audience targeting
 */
const CampaignManagement = () => {
  const [campaigns, setCampaigns] = useState([]);
  const [selectedCampaign, setSelectedCampaign] = useState(null);
  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('all');

  // Form state for campaign creation/editing
  const [campaignForm, setCampaignForm] = useState({
    name: '',
    description: '',
    type: 'email',
    target_audience: 'all',
    schedule_type: 'immediate',
    scheduled_at: '',
    content: {
      subject: '',
      body: '',
      action_text: '',
      action_url: ''
    },
    target_criteria: {},
    settings: {}
  });

  useEffect(() => {
    fetchCampaigns();
  }, []);

  const fetchCampaigns = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/marketing/campaigns');
      const result = await response.json();

      if (result.success) {
        setCampaigns(result.data);
      }
    } catch (error) {
      console.error('Failed to fetch campaigns:', error);
    } finally {
      setLoading(false);
    }
  };

  const createCampaign = async () => {
    try {
      const response = await fetch('/api/v1/marketing/campaigns', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(campaignForm)
      });

      const result = await response.json();

      if (result.success) {
        await fetchCampaigns();
        setIsCreateDialogOpen(false);
        resetForm();
      } else {
        alert('Failed to create campaign: ' + result.message);
      }
    } catch (error) {
      console.error('Failed to create campaign:', error);
      alert('Failed to create campaign');
    }
  };

  const launchCampaign = async (campaignId) => {
    try {
      const response = await fetch(`/api/v1/marketing/campaigns/${campaignId}/launch`, {
        method: 'POST'
      });

      const result = await response.json();

      if (result.success) {
        await fetchCampaigns();
      } else {
        alert('Failed to launch campaign: ' + result.message);
      }
    } catch (error) {
      console.error('Failed to launch campaign:', error);
    }
  };

  const pauseCampaign = async (campaignId) => {
    try {
      const response = await fetch(`/api/v1/marketing/campaigns/${campaignId}/pause`, {
        method: 'POST'
      });

      const result = await response.json();

      if (result.success) {
        await fetchCampaigns();
      }
    } catch (error) {
      console.error('Failed to pause campaign:', error);
    }
  };

  const resumeCampaign = async (campaignId) => {
    try {
      const response = await fetch(`/api/v1/marketing/campaigns/${campaignId}/resume`, {
        method: 'POST'
      });

      const result = await response.json();

      if (result.success) {
        await fetchCampaigns();
      }
    } catch (error) {
      console.error('Failed to resume campaign:', error);
    }
  };

  const cancelCampaign = async (campaignId) => {
    if (!confirm('Are you sure you want to cancel this campaign?')) return;

    try {
      const response = await fetch(`/api/v1/marketing/campaigns/${campaignId}/cancel`, {
        method: 'POST'
      });

      const result = await response.json();

      if (result.success) {
        await fetchCampaigns();
      }
    } catch (error) {
      console.error('Failed to cancel campaign:', error);
    }
  };

  const resetForm = () => {
    setCampaignForm({
      name: '',
      description: '',
      type: 'email',
      target_audience: 'all',
      schedule_type: 'immediate',
      scheduled_at: '',
      content: {
        subject: '',
        body: '',
        action_text: '',
        action_url: ''
      },
      target_criteria: {},
      settings: {}
    });
  };

  const getCampaignIcon = (type) => {
    switch (type) {
      case 'email': return <Mail className="h-4 w-4" />;
      case 'push': return <Smartphone className="h-4 w-4" />;
      case 'in_app': return <MessageSquare className="h-4 w-4" />;
      case 'sms': return <MessageSquare className="h-4 w-4" />;
      default: return <Mail className="h-4 w-4" />;
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'active': return 'default';
      case 'completed': return 'secondary';
      case 'draft': return 'outline';
      case 'paused': return 'destructive';
      case 'cancelled': return 'destructive';
      default: return 'outline';
    }
  };

  const filteredCampaigns = campaigns.filter(campaign => {
    if (activeTab === 'all') return true;
    return campaign.status === activeTab;
  });

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="p-6 space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Campaign Management</h1>
          <p className="text-gray-600">Create, manage, and monitor marketing campaigns</p>
        </div>
        <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
          <DialogTrigger asChild>
            <Button className="flex items-center gap-2">
              <Plus className="h-4 w-4" />
              Create Campaign
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Create New Campaign</DialogTitle>
              <DialogDescription>
                Create a new marketing campaign to engage your users
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Campaign Name</Label>
                  <Input
                    id="name"
                    value={campaignForm.name}
                    onChange={(e) => setCampaignForm({ ...campaignForm, name: e.target.value })}
                    placeholder="Enter campaign name"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="type">Campaign Type</Label>
                  <Select value={campaignForm.type} onValueChange={(value) => setCampaignForm({ ...campaignForm, type: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="email">Email</SelectItem>
                      <SelectItem value="push">Push Notification</SelectItem>
                      <SelectItem value="in_app">In-App Message</SelectItem>
                      <SelectItem value="sms">SMS</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={campaignForm.description}
                  onChange={(e) => setCampaignForm({ ...campaignForm, description: e.target.value })}
                  placeholder="Campaign description"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="target_audience">Target Audience</Label>
                  <Select value={campaignForm.target_audience} onValueChange={(value) => setCampaignForm({ ...campaignForm, target_audience: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Users</SelectItem>
                      <SelectItem value="subscribers">Subscribers</SelectItem>
                      <SelectItem value="active_users">Active Users</SelectItem>
                      <SelectItem value="inactive_users">Inactive Users</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="schedule_type">Schedule</Label>
                  <Select value={campaignForm.schedule_type} onValueChange={(value) => setCampaignForm({ ...campaignForm, schedule_type: value })}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="immediate">Send Immediately</SelectItem>
                      <SelectItem value="scheduled">Schedule for Later</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              {campaignForm.schedule_type === 'scheduled' && (
                <div className="space-y-2">
                  <Label htmlFor="scheduled_at">Schedule Date & Time</Label>
                  <Input
                    id="scheduled_at"
                    type="datetime-local"
                    value={campaignForm.scheduled_at}
                    onChange={(e) => setCampaignForm({ ...campaignForm, scheduled_at: e.target.value })}
                  />
                </div>
              )}

              {campaignForm.type === 'email' && (
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="subject">Email Subject</Label>
                    <Input
                      id="subject"
                      value={campaignForm.content.subject}
                      onChange={(e) => setCampaignForm({
                        ...campaignForm,
                        content: { ...campaignForm.content, subject: e.target.value }
                      })}
                      placeholder="Email subject line"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="body">Email Body</Label>
                    <Textarea
                      id="body"
                      value={campaignForm.content.body}
                      onChange={(e) => setCampaignForm({
                        ...campaignForm,
                        content: { ...campaignForm.content, body: e.target.value }
                      })}
                      placeholder="Email content"
                      rows={6}
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="action_text">Call-to-Action Text</Label>
                      <Input
                        id="action_text"
                        value={campaignForm.content.action_text}
                        onChange={(e) => setCampaignForm({
                          ...campaignForm,
                          content: { ...campaignForm.content, action_text: e.target.value }
                        })}
                        placeholder="e.g., Get Started"
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="action_url">Call-to-Action URL</Label>
                      <Input
                        id="action_url"
                        value={campaignForm.content.action_url}
                        onChange={(e) => setCampaignForm({
                          ...campaignForm,
                          content: { ...campaignForm.content, action_url: e.target.value }
                        })}
                        placeholder="https://example.com"
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="flex justify-end gap-2">
                <Button variant="outline" onClick={() => setIsCreateDialogOpen(false)}>
                  Cancel
                </Button>
                <Button onClick={createCampaign}>
                  Create Campaign
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {/* Campaign Status Tabs */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
        <TabsList>
          <TabsTrigger value="all">All Campaigns</TabsTrigger>
          <TabsTrigger value="draft">Draft</TabsTrigger>
          <TabsTrigger value="active">Active</TabsTrigger>
          <TabsTrigger value="completed">Completed</TabsTrigger>
          <TabsTrigger value="paused">Paused</TabsTrigger>
        </TabsList>

        <TabsContent value={activeTab} className="space-y-4">
          <div className="grid grid-cols-1 gap-4">
            {filteredCampaigns.map((campaign) => (
              <Card key={campaign.id}>
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div className="flex items-center gap-3">
                      {getCampaignIcon(campaign.type)}
                      <div>
                        <CardTitle>{campaign.name}</CardTitle>
                        <CardDescription>
                          {campaign.type} • Created {new Date(campaign.created_at).toLocaleDateString()}
                          {campaign.created_by && ` by ${campaign.created_by}`}
                        </CardDescription>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant={getStatusColor(campaign.status)}>
                        {campaign.status}
                      </Badge>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div className="flex items-center gap-2">
                      <Target className="h-4 w-4 text-gray-500" />
                      <span className="text-sm">
                        {campaign.metrics?.targets || 0} targets
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Mail className="h-4 w-4 text-gray-500" />
                      <span className="text-sm">
                        {campaign.metrics?.sent || 0} sent
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <BarChart3 className="h-4 w-4 text-gray-500" />
                      <span className="text-sm">
                        {campaign.metrics?.delivery_rate || 0}% delivered
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Users className="h-4 w-4 text-gray-500" />
                      <span className="text-sm">
                        {campaign.metrics?.engagement_score || 0}% engagement
                      </span>
                    </div>
                  </div>

                  <div className="flex justify-end gap-2">
                    {campaign.status === 'draft' && (
                      <Button
                        size="sm"
                        onClick={() => launchCampaign(campaign.id)}
                        className="flex items-center gap-1"
                      >
                        <Play className="h-3 w-3" />
                        Launch
                      </Button>
                    )}

                    {campaign.status === 'active' && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => pauseCampaign(campaign.id)}
                        className="flex items-center gap-1"
                      >
                        <Pause className="h-3 w-3" />
                        Pause
                      </Button>
                    )}

                    {campaign.status === 'paused' && (
                      <Button
                        size="sm"
                        onClick={() => resumeCampaign(campaign.id)}
                        className="flex items-center gap-1"
                      >
                        <Play className="h-3 w-3" />
                        Resume
                      </Button>
                    )}

                    {['draft', 'scheduled', 'active', 'paused'].includes(campaign.status) && (
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => cancelCampaign(campaign.id)}
                        className="flex items-center gap-1"
                      >
                        <Square className="h-3 w-3" />
                        Cancel
                      </Button>
                    )}

                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => window.open(`/campaigns/${campaign.id}/analytics`, '_blank')}
                      className="flex items-center gap-1"
                    >
                      <BarChart3 className="h-3 w-3" />
                      Analytics
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          {filteredCampaigns.length === 0 && (
            <Card>
              <CardContent className="text-center py-8">
                <p className="text-gray-500">No campaigns found for the selected status.</p>
                <Button
                  className="mt-4"
                  onClick={() => setIsCreateDialogOpen(true)}
                >
                  Create Your First Campaign
                </Button>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default CampaignManagement;