import { useCallback } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getProfile,
  updateProfile,
  getCurrentUser,
  type UpdateProfilePayload,
  type ProfileResponse,
} from "@/lib/api/profile-management";
import { toast } from "sonner";

export function useProfile() {
  const queryClient = useQueryClient();

  const {
    data: profileResponse,
    isLoading,
    error,
    refetch,
  } = useQuery<ProfileResponse>({
    queryKey: ["profile"],
    queryFn: getProfile,
    retry: 1,
  });

  const updateMutation = useMutation({
    mutationFn: (data: UpdateProfilePayload) => updateProfile(data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ["profile"] });
      queryClient.invalidateQueries({ queryKey: ["currentUser"] });
      toast.success(response.message || "Profile updated successfully");
    },
    onError: (error: any) => {
      const message = error?.message || "Failed to update profile";
      toast.error(message);
    },
  });

  const {
    data: currentUserResponse,
    isLoading: isLoadingCurrentUser,
    refetch: refetchCurrentUser,
  } = useQuery<ProfileResponse>({
    queryKey: ["currentUser"],
    queryFn: getCurrentUser,
    staleTime: 5 * 60 * 1000,
  });

  const updateProfileData = useCallback(
    (data: UpdateProfilePayload) => {
      return updateMutation.mutateAsync(data);
    },
    [updateMutation]
  );

  const user = profileResponse?.data;
  const thirdParty = user?.thirdParty;

  return {
    profile: user,
    thirdParty: thirdParty,
    thirdPartyDetails: thirdParty?.thirdPartyDetails,
    profileCompletion: thirdParty?.profileCompletion || 0,

    isLoading,
    isUpdating: updateMutation.isPending,
    isLoadingCurrentUser,

    updateProfile: updateProfileData,
    refetch,
    refreshCurrentUser: refetchCurrentUser,

    currentUser: currentUserResponse?.data,
    error,
  };
}