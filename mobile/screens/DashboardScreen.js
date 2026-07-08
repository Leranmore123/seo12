import React, { useState, useEffect, useCallback } from 'react';
import { StyleSheet, View, Text, ScrollView, RefreshControl, TouchableOpacity, ActivityIndicator, Alert } from 'react-native';

export default function DashboardScreen({ userToken, serverUrl, onLogout }) {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchStats = async () => {
    try {
      const response = await fetch(`${serverUrl}/api/stats.php`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${userToken}`,
          'Content-Type': 'application/json',
        },
      });

      const data = await response.json();

      if (response.status === 200 && data.success) {
        setStats(data.stats);
      } else {
        Alert.alert('Session Expired', 'Please log in again.');
        onLogout();
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Connection Error', 'Could not refresh stats from server.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchStats();
  }, []);

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#4f46e5" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>SEO Dashboard</Text>
          <Text style={styles.headerSub}>Live Server Overview</Text>
        </View>
        <TouchableOpacity style={styles.logoutButton} onPress={onLogout}>
          <Text style={styles.logoutText}>Log Out</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#4f46e5']} />
        }
      >
        {/* Stats Grid */}
        <View style={styles.grid}>
          <View style={[styles.card, styles.indigoCard]}>
            <Text style={styles.cardTitle}>Total Projects</Text>
            <Text style={styles.cardVal}>{stats?.total_projects ?? 0}</Text>
            <Text style={styles.cardSub}>Active campaign trackers</Text>
          </View>

          <View style={[styles.card, styles.emeraldCard]}>
            <Text style={styles.cardTitle}>Active Accounts</Text>
            <Text style={styles.cardVal}>{stats?.active_social_accounts ?? 0}</Text>
            <Text style={styles.cardSub}>{stats?.total_social_accounts ?? 0} total profiles saved</Text>
          </View>

          <View style={[styles.card, styles.skyCard]}>
            <Text style={styles.cardTitle}>Backlinks Created</Text>
            <Text style={styles.cardVal}>{stats?.total_backlinks_created ?? 0}</Text>
            <Text style={styles.cardSub}>Successful auto postings</Text>
          </View>

          <View style={[styles.card, styles.amberCard]}>
            <Text style={styles.cardTitle}>Pending Tasks</Text>
            <Text style={styles.cardVal}>{stats?.pending_queue_tasks ?? 0}</Text>
            <Text style={styles.cardSub}>Active postings in queue</Text>
          </View>

          <View style={[styles.card, styles.roseCard]}>
            <Text style={styles.cardTitle}>Failed Postings</Text>
            <Text style={styles.cardVal}>{stats?.failed_queue_tasks ?? 0}</Text>
            <Text style={styles.cardSub}>Failures needing review</Text>
          </View>
        </View>

        {/* Tip section */}
        <View style={styles.tipBox}>
          <Text style={styles.tipHeader}>💡 Quick Tip</Text>
          <Text style={styles.tipText}>
            Pull down on this page to refresh the live metrics. The background Selenium engines execute tasks asynchronously on the AWS server.
          </Text>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingTop: 20,
    paddingBottom: 16,
    backgroundColor: '#ffffff',
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: '#0f172a',
  },
  headerSub: {
    fontSize: 12,
    color: '#64748b',
    fontWeight: '600',
  },
  logoutButton: {
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#ef4444',
  },
  logoutText: {
    color: '#ef4444',
    fontSize: 13,
    fontWeight: '700',
  },
  scrollContent: {
    padding: 20,
  },
  grid: {
    flexDirection: 'column',
    gap: 16,
  },
  card: {
    borderRadius: 12,
    padding: 20,
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.03,
    shadowRadius: 8,
    elevation: 1,
  },
  indigoCard: { backgroundColor: '#e0e7ff', borderLeftWidth: 6, borderLeftColor: '#4f46e5' },
  emeraldCard: { backgroundColor: '#d1fae5', borderLeftWidth: 6, borderLeftColor: '#10b981' },
  skyCard: { backgroundColor: '#e0f2fe', borderLeftWidth: 6, borderLeftColor: '#0ea5e9' },
  amberCard: { backgroundColor: '#fef3c7', borderLeftWidth: 6, borderLeftColor: '#f59e0b' },
  roseCard: { backgroundColor: '#ffe4e6', borderLeftWidth: 6, borderLeftColor: '#f43f5e' },
  cardTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#475569',
    textTransform: 'uppercase',
  },
  cardVal: {
    fontSize: 32,
    fontWeight: '800',
    color: '#0f172a',
    marginVertical: 4,
  },
  cardSub: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '500',
  },
  tipBox: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginTop: 24,
  },
  tipHeader: {
    fontWeight: 'bold',
    fontSize: 14,
    color: '#334155',
    marginBottom: 4,
  },
  tipText: {
    fontSize: 12,
    color: '#64748b',
    lineHeight: 18,
  },
});
